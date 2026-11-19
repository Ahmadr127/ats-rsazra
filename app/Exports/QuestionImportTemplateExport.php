<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionImportTemplateExport implements WithMultipleSheets
{
    public const HEADINGS = [
        'Tipe',
        'Pertanyaan',
        'Poin',
        'Opsi A',
        'Opsi B',
        'Opsi C',
        'Opsi D',
        'Kunci Jawaban',
    ];

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new QuestionImportSheet,
            new QuestionImportExampleSheet,
            new QuestionImportGuideSheet,
        ];
    }
}
