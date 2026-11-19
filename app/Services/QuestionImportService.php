<?php

namespace App\Services;

use Illuminate\Support\Collection;

class QuestionImportService
{
    public const MAX_QUESTIONS = 500;

    /**
     * @return array{questions: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    public function parse(Collection $rows): array
    {
        $questions = [];
        $errors = [];

        if ($rows->count() > self::MAX_QUESTIONS) {
            return [
                'questions' => [],
                'errors' => ['Jumlah soal melebihi batas maksimal '.self::MAX_QUESTIONS.' soal.'],
            ];
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $row instanceof Collection ? $row->toArray() : (array) $row;

            $tipe = $this->normalizeTipe($this->text($data['tipe'] ?? null));
            $pertanyaan = $this->text($data['pertanyaan'] ?? null);
            $poin = filter_var($this->text($data['poin'] ?? null), FILTER_VALIDATE_INT);
            $options = [
                ['teks_opsi' => $this->text($data['opsi_a'] ?? null)],
                ['teks_opsi' => $this->text($data['opsi_b'] ?? null)],
                ['teks_opsi' => $this->text($data['opsi_c'] ?? null)],
                ['teks_opsi' => $this->text($data['opsi_d'] ?? null)],
            ];
            $kunci = strtoupper($this->text($data['kunci_jawaban'] ?? null));

            if ($tipe === null) {
                $errors[] = "Baris {$rowNumber}: Tipe harus Pilihan Ganda, Esai, atau Unggah Gambar.";

                continue;
            }

            if ($pertanyaan === '') {
                $errors[] = "Baris {$rowNumber}: Pertanyaan wajib diisi.";
            } elseif (mb_strlen($pertanyaan) > 2000) {
                $errors[] = "Baris {$rowNumber}: Pertanyaan maksimal 2000 karakter.";
            }

            if ($poin === false || $poin < 1 || $poin > 100) {
                $errors[] = "Baris {$rowNumber}: Poin harus angka bulat antara 1 dan 100.";
            }

            $correctOption = null;

            if ($tipe === 'mc') {
                $correctOption = $this->validateMcOptions($options, $kunci, $rowNumber, $errors);
            }

            if ($pertanyaan === '' || $poin === false || $poin < 1 || $poin > 100 || ($tipe === 'mc' && $correctOption === null)) {
                continue;
            }

            $questions[] = [
                'id' => null,
                'tipe' => $tipe,
                'pertanyaan' => $pertanyaan,
                'nilai_poin' => $poin,
                'gambar_path' => null,
                'correct_option' => $correctOption ?? 0,
                'options' => $tipe === 'mc'
                    ? $options
                    : [['teks_opsi' => ''], ['teks_opsi' => ''], ['teks_opsi' => ''], ['teks_opsi' => '']],
            ];
        }

        if ($errors === [] && $questions === []) {
            $errors[] = 'File tidak berisi soal.';
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * @param  array<int, array{teks_opsi: string}>  $options
     * @param  array<int, string>  $errors
     */
    private function validateMcOptions(array $options, string $kunci, int $rowNumber, array &$errors): ?int
    {
        $previousEmpty = false;

        foreach ($options as $index => $option) {
            if ($option['teks_opsi'] === '') {
                $previousEmpty = true;

                continue;
            }

            if ($previousEmpty) {
                $errors[] = "Baris {$rowNumber}: Opsi tidak boleh ada celah kosong.";

                return null;
            }
        }

        if ($options[0]['teks_opsi'] === '' || $options[1]['teks_opsi'] === '') {
            $errors[] = "Baris {$rowNumber}: Pilihan Ganda minimal mengisi Opsi A dan Opsi B.";

            return null;
        }

        if ($kunci === '' || $kunci === '-') {
            $errors[] = "Baris {$rowNumber}: Kunci Jawaban wajib diisi A, B, C, atau D.";

            return null;
        }

        $index = array_search($kunci, ['A', 'B', 'C', 'D'], true);

        if ($index === false) {
            $errors[] = "Baris {$rowNumber}: Kunci Jawaban harus A, B, C, atau D.";

            return null;
        }

        if ($options[$index]['teks_opsi'] === '') {
            $errors[] = "Baris {$rowNumber}: Kunci Jawaban menunjuk opsi yang kosong.";

            return null;
        }

        return $index;
    }

    private function normalizeTipe(string $tipe): ?string
    {
        $tipe = strtolower(str_replace([' ', '-', '_'], '', $tipe));

        return match ($tipe) {
            'mc', 'pilihanganda' => 'mc',
            'essay', 'esai' => 'essay',
            'image', 'unggahgambar' => 'image',
            default => null,
        };
    }

    private function text(mixed $value): string
    {
        if ($value === null || ! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
