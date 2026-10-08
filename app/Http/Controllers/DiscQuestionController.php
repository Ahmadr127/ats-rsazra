<?php

namespace App\Http\Controllers;

use App\Enums\DiscDimension;
use App\Models\DiscQuestion;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $request->user()->requirePermission(Permissions::DISC_QUESTION_VIEW);

        $query = DiscQuestion::with('words')->withCount(['words', 'answers'])->orderBy('urutan')->orderBy('id');

        if ($search = $request->query('q')) {
            $query->whereHas('words', fn ($q) => $q->where('teks', 'like', "%{$search}%"));
        }

        $questions = $query->paginate(20)->withQueryString();

        return view('disc-questions.index', compact('questions'));
    }

    public function create(): View
    {
        auth()->user()->requirePermission(Permissions::DISC_QUESTION_CREATE);

        return view('disc-questions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::DISC_QUESTION_CREATE);

        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($validated): void {
            $question = DiscQuestion::create(['urutan' => $validated['urutan']]);

            foreach ($validated['words'] as $wordData) {
                $question->words()->create([
                    'teks' => $wordData['teks'],
                    'dimensi' => $wordData['dimensi'],
                ]);
            }
        });

        return redirect()->route('soal-disc.index')
            ->with('success', 'Soal DiSC berhasil dibuat.');
    }

    public function edit(DiscQuestion $soalDisc): View
    {
        auth()->user()->requirePermission(Permissions::DISC_QUESTION_UPDATE);

        $soalDisc->load('words');

        return view('disc-questions.edit', ['question' => $soalDisc]);
    }

    public function update(Request $request, DiscQuestion $soalDisc): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::DISC_QUESTION_UPDATE);

        $validated = $request->validate($this->rules($soalDisc));

        DB::transaction(function () use ($validated, $soalDisc): void {
            $soalDisc->update(['urutan' => $validated['urutan']]);

            $submittedIds = collect($validated['words'])->pluck('id')->filter()->all();
            $soalDisc->words()->whereNotIn('id', $submittedIds)->delete();

            $existingWords = $soalDisc->words()->whereIn('id', $submittedIds)->get()->keyBy('id');

            foreach ($validated['words'] as $wordData) {
                $attributes = [
                    'teks' => $wordData['teks'],
                    'dimensi' => $wordData['dimensi'],
                ];

                if (! empty($wordData['id'])) {
                    $existingWords->get($wordData['id'])->update($attributes);
                } else {
                    $soalDisc->words()->create($attributes);
                }
            }
        });

        return redirect()->route('soal-disc.index')
            ->with('success', 'Soal DiSC berhasil diperbarui.');
    }

    public function destroy(DiscQuestion $soalDisc): RedirectResponse
    {
        auth()->user()->requirePermission(Permissions::DISC_QUESTION_DELETE);

        if ($soalDisc->answers()->exists()) {
            return back()->withErrors(['soal' => 'Soal tidak dapat dihapus karena sudah memiliki jawaban kandidat.']);
        }

        $soalDisc->delete();

        return redirect()->route('soal-disc.index')
            ->with('success', 'Soal DiSC berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function rules(?DiscQuestion $question = null): array
    {
        $wordIdRule = ['nullable', 'integer'];

        if ($question) {
            $wordIdRule[] = Rule::exists('disc_question_words', 'id')->where('disc_question_id', $question->id);
        }

        return [
            'urutan' => ['required', 'integer', 'min:1', 'max:9999'],
            'words' => [
                'required',
                'array',
                'size:4',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value) || count($value) !== 4) {
                        return;
                    }

                    $dimensions = array_map(fn ($word) => $word['dimensi'] ?? null, $value);
                    sort($dimensions);

                    if ($dimensions !== ['C', 'D', 'I', 'S']) {
                        $fail('Keempat kata harus mencakup tepat satu dari tiap dimensi: D, I, S, C.');
                    }
                },
            ],
            'words.*.id' => $wordIdRule,
            'words.*.teks' => ['required', 'string', 'max:255'],
            'words.*.dimensi' => ['required', Rule::enum(DiscDimension::class)],
        ];
    }
}
