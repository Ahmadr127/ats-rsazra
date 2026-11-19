<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Logging\LogContext;
use App\Models\TestSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TestController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $submission = TestSubmission::with([
            'snapshot.questions.options',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            $questions = $submission->snapshot->questions;

            return view('test.show', compact('submission', 'questions'));
        }

        if ($submission->started_at === null) {
            $submission->update(['started_at' => now()]);
        }

        if ($submission->isExpired()) {
            return $this->doSubmit($submission, [], []);
        }

        $questions = $submission->snapshot->questions;

        return view('test.show', compact('submission', 'questions'));
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $submission = TestSubmission::with([
            'snapshot.questions.options',
            'application.candidate',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            return redirect()->route('tes.show', $token);
        }

        $questions = $submission->snapshot->questions;
        $rules = [];
        $messages = [];

        foreach ($questions as $question) {
            if ($question->tipe !== QuestionType::Image) {
                continue;
            }

            $key = "answers.{$question->id}";
            $rules[$key] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            $messages["{$key}.required"] = 'Gambar jawaban wajib diunggah.';
            $messages["{$key}.image"] = 'Jawaban harus berupa gambar.';
            $messages["{$key}.mimes"] = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';
            $messages["{$key}.max"] = 'Ukuran gambar maksimal 5 MB.';
        }

        $request->validate($rules, $messages);

        $answers = $request->input('answers', []);
        $files = $request->file('answers', []);

        return $this->doSubmit($submission, $answers, $files);
    }

    private function doSubmit(TestSubmission $submission, array $answers, array $files = []): RedirectResponse
    {
        $storedPaths = [];

        try {
            DB::transaction(function () use ($submission, $answers, $files, &$storedPaths): void {
                $locked = TestSubmission::lockForUpdate()->findOrFail($submission->id);

                if ($locked->isSubmitted()) {
                    return;
                }

                $totalSkor = 0;
                $questions = $submission->snapshot->questions;

                foreach ($questions as $question) {
                    $selectedOptionId = $answers[$question->id] ?? null;

                    $skor = null;
                    $snapshotOptionId = null;
                    $jawabanTeks = null;
                    $gambarJawabanPath = null;

                    if ($question->tipe === QuestionType::Mc) {
                        $snapshotOptionId = $selectedOptionId ? (int) $selectedOptionId : null;
                        $correctOption = $question->options->firstWhere('is_correct', true);

                        if ($correctOption && $snapshotOptionId === $correctOption->id) {
                            $skor = $question->nilai_poin;
                        } else {
                            $skor = 0;
                        }

                        $totalSkor += $skor;
                    } elseif ($question->tipe === QuestionType::Essay) {
                        $jawabanTeks = $answers[$question->id] ?? null;
                    } elseif ($question->tipe === QuestionType::Image) {
                        $file = $files[$question->id] ?? null;

                        if ($file instanceof UploadedFile) {
                            $gambarJawabanPath = $this->storeAnswerImage($file, $submission->application_id, $question->id);
                            $storedPaths[] = $gambarJawabanPath;
                        }
                    }

                    $submission->answers()->create([
                        'vacancy_test_snapshot_question_id' => $question->id,
                        'vacancy_test_snapshot_option_id' => $snapshotOptionId,
                        'jawaban_teks' => $jawabanTeks,
                        'gambar_jawaban_path' => $gambarJawabanPath,
                        'skor' => $skor,
                        'is_reviewed' => $question->tipe === QuestionType::Mc,
                    ]);
                }

                $hasManualReview = $questions->contains(fn ($q) => in_array($q->tipe, [QuestionType::Essay, QuestionType::Image], true));

                $submission->update([
                    'submitted_at' => now(),
                    'total_skor' => $hasManualReview ? null : $totalSkor,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);

            throw $e;
        }

        Log::info('Competency test submitted', array_merge(LogContext::make(), [
            'submission_id' => $submission->id,
            'application_id' => $submission->application_id,
        ]));

        return redirect()->route('tes.show', $submission->token);
    }

    private function storeAnswerImage(UploadedFile $file, int $applicationId, int $questionId): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $questionId.'-'.Str::uuid()->toString().'.'.$extension;

        return $file->storeAs("test-answer-images/{$applicationId}", $filename, 'public');
    }
}
