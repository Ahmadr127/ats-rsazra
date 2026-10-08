<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\QuestionBankTemplate;
use Illuminate\Database\Seeder;

class QuestionBankTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = QuestionBankTemplate::firstOrCreate(
            ['nama' => 'Tes Kompetensi Dasar'],
        );

        $questions = [
            [
                'tipe' => QuestionType::Mc,
                'pertanyaan' => 'Manakah yang merupakan ibukota Indonesia?',
                'nilai_poin' => 10,
                'options' => [
                    ['teks_opsi' => 'Jakarta', 'is_correct' => true],
                    ['teks_opsi' => 'Bandung', 'is_correct' => false],
                    ['teks_opsi' => 'Surabaya', 'is_correct' => false],
                    ['teks_opsi' => 'Medan', 'is_correct' => false],
                ],
            ],
            [
                'tipe' => QuestionType::Mc,
                'pertanyaan' => 'Hasil dari 12 + 15 x 2 adalah...',
                'nilai_poin' => 10,
                'options' => [
                    ['teks_opsi' => '54', 'is_correct' => false],
                    ['teks_opsi' => '42', 'is_correct' => true],
                    ['teks_opsi' => '39', 'is_correct' => false],
                    ['teks_opsi' => '27', 'is_correct' => false],
                ],
            ],
            [
                'tipe' => QuestionType::Mc,
                'pertanyaan' => 'Sinonim kata "efektif" adalah...',
                'nilai_poin' => 10,
                'options' => [
                    ['teks_opsi' => 'Berhasil guna', 'is_correct' => true],
                    ['teks_opsi' => 'Lambat', 'is_correct' => false],
                    ['teks_opsi' => 'Boros', 'is_correct' => false],
                    ['teks_opsi' => 'Sulit', 'is_correct' => false],
                ],
            ],
            [
                'tipe' => QuestionType::Essay,
                'pertanyaan' => 'Ceritakan pengalaman kerja yang paling berkesan dan pelajaran yang Anda ambil.',
                'nilai_poin' => 20,
                'options' => [],
            ],
        ];

        foreach ($questions as $index => $data) {
            $question = $template->questions()->firstOrCreate(
                ['urutan' => $index + 1],
                [
                    'tipe' => $data['tipe'],
                    'pertanyaan' => $data['pertanyaan'],
                    'nilai_poin' => $data['nilai_poin'],
                ]
            );

            foreach ($data['options'] as $option) {
                $question->options()->firstOrCreate(
                    ['teks_opsi' => $option['teks_opsi']],
                    ['is_correct' => $option['is_correct']],
                );
            }
        }
    }
}
