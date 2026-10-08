<?php

namespace App\Http\Controllers;

use App\Enums\MbtiPole;
use App\Models\MbtiQuestion;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MbtiQuestionController extends Controller
{
    /** @return array<string, list<MbtiPole>> */
    public static function polesByDikotomi(): array
    {
        return [
            'EI' => [MbtiPole::E, MbtiPole::I],
            'SN' => [MbtiPole::S, MbtiPole::N],
            'TF' => [MbtiPole::T, MbtiPole::F],
            'JP' => [MbtiPole::J, MbtiPole::P],
        ];
    }

    public function index(Request $request): View
    {
        $request->user()->requirePermission(Permissions::MBTI_QUESTION_VIEW);

        $query = MbtiQuestion::withCount('answers')->orderBy('urutan')->orderBy('id');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search): void {
                $q->where('pernyataan_a', 'like', "%{$search}%")
                    ->orWhere('pernyataan_b', 'like', "%{$search}%");
            });
        }

        if ($dikotomi = $request->query('dikotomi')) {
            $query->where('dikotomi', $dikotomi);
        }

        $questions = $query->paginate(20)->withQueryString();

        return view('mbti-questions.index', compact('questions'));
    }

    public function create(): View
    {
        auth()->user()->requirePermission(Permissions::MBTI_QUESTION_CREATE);

        return view('mbti-questions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::MBTI_QUESTION_CREATE);

        $validated = $request->validate($this->rules());

        MbtiQuestion::create($validated);

        return redirect()->route('soal-mbti.index')
            ->with('success', 'Soal MBTI berhasil dibuat.');
    }

    public function edit(MbtiQuestion $soalMbti): View
    {
        auth()->user()->requirePermission(Permissions::MBTI_QUESTION_UPDATE);

        return view('mbti-questions.edit', ['question' => $soalMbti]);
    }

    public function update(Request $request, MbtiQuestion $soalMbti): RedirectResponse
    {
        $request->user()->requirePermission(Permissions::MBTI_QUESTION_UPDATE);

        $validated = $request->validate($this->rules());

        $soalMbti->update($validated);

        return redirect()->route('soal-mbti.index')
            ->with('success', 'Soal MBTI berhasil diperbarui.');
    }

    public function destroy(MbtiQuestion $soalMbti): RedirectResponse
    {
        auth()->user()->requirePermission(Permissions::MBTI_QUESTION_DELETE);

        if ($soalMbti->answers()->exists()) {
            return back()->withErrors(['soal' => 'Soal tidak dapat dihapus karena sudah memiliki jawaban kandidat.']);
        }

        $soalMbti->delete();

        return redirect()->route('soal-mbti.index')
            ->with('success', 'Soal MBTI berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        $polesByDikotomi = array_map(
            fn ($poles) => array_map(fn (MbtiPole $pole) => $pole->value, $poles),
            self::polesByDikotomi()
        );

        return [
            'urutan' => ['required', 'integer', 'min:1', 'max:9999'],
            'dikotomi' => ['required', Rule::in(array_keys($polesByDikotomi))],
            'kutub_a' => [
                'required',
                Rule::enum(MbtiPole::class),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $dikotomi = request()->input('dikotomi');
                    $allowed = self::polesByDikotomi()[$dikotomi] ?? [];
                    $allowedValues = array_map(fn (MbtiPole $pole) => $pole->value, $allowed);

                    if (! in_array($value, $allowedValues, true)) {
                        $fail('Kutub A harus salah satu kutub dari dikotomi '.$dikotomi.' ('.implode('/', $allowedValues).').');
                    }
                },
            ],
            'pernyataan_a' => ['required', 'string', 'max:1000'],
            'pernyataan_b' => ['required', 'string', 'max:1000'],
        ];
    }
}
