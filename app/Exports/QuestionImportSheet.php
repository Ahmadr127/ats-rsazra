<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionImportSheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Soal';
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    public function array(): array
    {
        return QuestionImportExampleSheet::ROWS;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return QuestionImportTemplateExport::HEADINGS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->setDataValidation('A2:A501', $this->listValidation('"Pilihan Ganda,Esai,Unggah Gambar"', 'Pilih salah satu tipe soal.'));
                $sheet->setDataValidation('H2:H501', $this->listValidation('"A,B,C,D,-"', 'Isi A–D untuk Pilihan Ganda, atau "-" untuk tipe lain.'));
                $sheet->setDataValidation('C2:C501', (new DataValidation)
                    ->setType(DataValidation::TYPE_WHOLE)
                    ->setOperator(DataValidation::OPERATOR_BETWEEN)
                    ->setFormula1('1')
                    ->setFormula2('100')
                    ->setAllowBlank(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Poin tidak valid')
                    ->setError('Poin harus antara 1 dan 100.')
                    ->setSqref('C2:C501'));
            },
        ];
    }

    private function listValidation(string $formula, string $prompt): DataValidation
    {
        return (new DataValidation)
            ->setType(DataValidation::TYPE_LIST)
            ->setFormula1($formula)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowInputMessage(true)
            ->setPromptTitle('Panduan pengisian')
            ->setPrompt($prompt)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Nilai tidak valid')
            ->setError('Pilih salah satu nilai yang tersedia.');
    }
}
