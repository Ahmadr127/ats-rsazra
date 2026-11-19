<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\QuestionBankTemplate;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionBankTemplateController extends Controller
{
    private const IMAGE_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    public function index(Request $request): View
    {
        $request->user()->requirePermission(Permissions::QUESTION_BANK_VIEW);

        $query = QuestionBankTemplate::withCount('questions')
            ->latest();

        if ($search = $request->query('q')) {
            $query->where('nama', 'ilike', "%{$search}%");
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('question-bank-templates.index', compact('templates'));
    }

    public function create(): View
    {
        auth()->user()->requirePermission(Permissions::QUESTION_BANK_CREATE);

        return view('question-bank-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::QUESTION_BANK_CREATE);

        $validated = $this->validateTemplate($request);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, &$storedPaths): void {
                $template = QuestionBankTemplate::create([
                    'nama' => $validated['nama'],
                ]);

                foreach ($validated['questions'] as $index => $questionData) {
                    $question = $template->questions()->create([
                        'tipe' => $questionData['tipe'],
                        'pertanyaan' => $questionData['pertanyaan'],
                        'nilai_poin' => $questionData['nilai_poin'],
                        'urutan' => $index + 1,
                    ]);

                    if (! empty($questionData['gambar'])) {
                        $path = $this->storeQuestionImage($questionData['gambar'], $template->id, $question->id);
                        $storedPaths[] = $path;
                        $question->update(['gambar_path' => $path]);
                    }

                    if ($questionData['tipe'] === QuestionType::Mc->value) {
                        $correctIndex = (int) ($questionData['correct_option'] ?? 0);
                        foreach ($questionData['options'] as $optIndex => $option) {
                            $question->options()->create([
                                'teks_opsi' => $option['teks_opsi'],
                                'is_correct' => $optIndex === $correctIndex,
                            ]);
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);

            throw $e;
        }

        return redirect()->route('template-bank-soal.index')
            ->with('success', 'Template bank soal berhasil dibuat.');
    }

    public function edit(QuestionBankTemplate $templateBankSoal): View
    {
        auth()->user()->requirePermission(Permissions::QUESTION_BANK_UPDATE);

        $templateBankSoal->load('questions.options');

        return view('question-bank-templates.edit', ['template' => $templateBankSoal]);
    }

    public function update(Request $request, QuestionBankTemplate $templateBankSoal): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::QUESTION_BANK_UPDATE);

        $validated = $this->validateTemplate($request, $templateBankSoal);

        $storedPaths = [];
        $pathsToDelete = [];

        try {
            DB::transaction(function () use ($validated, $templateBankSoal, &$storedPaths, &$pathsToDelete): void {
                $templateBankSoal->update(['nama' => $validated['nama']]);

                $submittedIds = collect($validated['questions'])->pluck('id')->filter()->all();
                $removedQuestions = $templateBankSoal->questions()
                    ->whereNotIn('id', $submittedIds)
                    ->get();

                foreach ($removedQuestions as $removedQuestion) {
                    if ($removedQuestion->gambar_path) {
                        $pathsToDelete[] = $removedQuestion->gambar_path;
                    }
                }
                $templateBankSoal->questions()->whereNotIn('id', $submittedIds)->delete();

                foreach ($validated['questions'] as $index => $questionData) {
                    $attributes = [
                        'tipe' => $questionData['tipe'],
                        'pertanyaan' => $questionData['pertanyaan'],
                        'gambar_path' => null,
                        'nilai_poin' => $questionData['nilai_poin'],
                        'urutan' => $index + 1,
                    ];

                    $question = ! empty($questionData['id'])
                        ? $templateBankSoal->questions()->findOrFail($questionData['id'])
                        : $templateBankSoal->questions()->create($attributes);

                    $attributes['gambar_path'] = $question->gambar_path;

                    if ($question->gambar_path && (! empty($questionData['gambar']) || $this->shouldRemoveImage($questionData))) {
                        $pathsToDelete[] = $question->gambar_path;
                    }

                    if (! empty($questionData['gambar'])) {
                        $attributes['gambar_path'] = $this->storeQuestionImage($questionData['gambar'], $templateBankSoal->id, $question->id);
                        $storedPaths[] = $attributes['gambar_path'];
                    } elseif ($this->shouldRemoveImage($questionData)) {
                        $attributes['gambar_path'] = null;
                    }

                    $question->update($attributes);

                    if ($questionData['tipe'] === QuestionType::Mc->value) {
                        $question->options()->delete();
                        $correctIndex = (int) ($questionData['correct_option'] ?? 0);
                        foreach ($questionData['options'] as $optIndex => $option) {
                            $question->options()->create([
                                'teks_opsi' => $option['teks_opsi'],
                                'is_correct' => $optIndex === $correctIndex,
                            ]);
                        }
                    } else {
                        $question->options()->delete();
                    }
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);

            throw $e;
        }

        Storage::disk('public')->delete($pathsToDelete);

        return redirect()->route('template-bank-soal.index')
            ->with('success', 'Template bank soal berhasil diperbarui.');
    }

    public function destroy(QuestionBankTemplate $templateBankSoal): RedirectResponse
    {
        auth()->user()->requirePermission(Permissions::QUESTION_BANK_DELETE);

        $paths = $templateBankSoal->questions()
            ->whereNotNull('gambar_path')
            ->pluck('gambar_path')
            ->all();

        $templateBankSoal->delete();

        Storage::disk('public')->delete($paths);

        return redirect()->route('template-bank-soal.index')
            ->with('success', 'Template bank soal berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTemplate(Request $request, ?QuestionBankTemplate $template = null): array
    {
        $nameRule = $template
            ? Rule::unique('question_bank_templates', 'nama')->ignore($template->id)
            : Rule::unique('question_bank_templates', 'nama');

        return $request->validate([
            'nama' => ['required', 'string', 'max:255', $nameRule],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => [
                'nullable',
                'integer',
                Rule::exists('questions', 'id')->where('question_bank_template_id', $template?->id ?? 0),
            ],
            'questions.*.tipe' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.pertanyaan' => ['required', 'string'],
            'questions.*.nilai_poin' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.gambar' => self::IMAGE_RULES,
            'questions.*.gambar_lama' => ['nullable', 'string'],
            'questions.*.hapus_gambar' => ['nullable', 'boolean'],
            'questions.*.options' => ['required_if:questions.*.tipe,mc', 'array', 'min:2'],
            'questions.*.options.*.teks_opsi' => ['required_if:questions.*.tipe,mc', 'string'],
            'questions.*.correct_option' => ['required_if:questions.*.tipe,mc', 'integer'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $questionData
     */
    private function shouldRemoveImage(array $questionData): bool
    {
        return filter_var($questionData['hapus_gambar'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function storeQuestionImage(UploadedFile $file, int $templateId, int $questionId): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $questionId.'-'.Str::uuid()->toString().'.'.$extension;

        return $file->storeAs("question-images/{$templateId}", $filename, 'public');
    }
}
