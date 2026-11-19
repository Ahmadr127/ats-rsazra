<?php

namespace App\Models;

use Database\Factories\VacancyTestSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VacancyTestSnapshot extends Model
{
    /** @use HasFactory<VacancyTestSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'vacancy_test_id',
        'batas_waktu_menit',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu_menit' => 'integer',
        ];
    }

    public function vacancyTest(): BelongsTo
    {
        return $this->belongsTo(VacancyTest::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(VacancyTestSnapshotQuestion::class)
            ->orderBy('urutan');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TestSubmission::class);
    }

    public function totalNilaiMaksimal(): int
    {
        return $this->questions->sum('nilai_poin');
    }

    public static function createFromVacancyTest(VacancyTest $vacancyTest): self
    {
        $vacancyTest->load('questions.options');

        $snapshot = self::create([
            'vacancy_test_id' => $vacancyTest->id,
            'batas_waktu_menit' => $vacancyTest->batas_waktu_menit,
        ]);

        $vacancyTest->questions->each(function (Question $question) use ($snapshot) {
            $snapshotQuestion = $snapshot->questions()->create([
                'urutan' => $question->pivot->urutan,
                'tipe' => $question->tipe->value,
                'pertanyaan' => $question->pertanyaan,
                'gambar_path' => self::copyQuestionImage($question->gambar_path, $snapshot->id, $question->pivot->urutan),
                'nilai_poin' => $question->nilai_poin,
            ]);

            $question->options->each(function (QuestionOption $option) use ($snapshotQuestion) {
                $snapshotQuestion->options()->create([
                    'teks_opsi' => $option->teks_opsi,
                    'is_correct' => $option->is_correct,
                ]);
            });
        });

        return $snapshot;
    }

    private static function copyQuestionImage(?string $sourcePath, int $snapshotId, int $urutan): ?string
    {
        if (! $sourcePath) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = $urutan.'-'.Str::uuid()->toString();
        $targetPath = 'test-snapshot-images/'.$snapshotId.'/'.$filename.($extension ? '.'.$extension : '');

        $disk->copy($sourcePath, $targetPath);

        return $targetPath;
    }
}
