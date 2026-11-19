<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionImportExampleSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Contoh';
    }

    /**
     * Satu baris contoh valid untuk setiap tipe soal (mc, essay, image).
     *
     * @var array<int, array<int, string|int>>
     */
    public const ROWS = [
        ['Pilihan Ganda', 'Apa fungsi utama hemoglobin?', 10, 'Mengangkut oksigen', 'Mencerna makanan', 'Melawan infeksi', 'Mengatur gula darah', 'A'],
        ['Esai', 'Jelaskan prosedur sterilisasi alat medis.', 20, '', '', '', '', '-'],
        ['Unggah Gambar', 'Unggah foto hasil pemeriksaan Anda.', 15, '', '', '', '', '-'],
    ];

    /**
     * @return array<int, array<int, string|int>>
     */
    public function array(): array
    {
        return self::ROWS;
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

        return [1 => ['font' => ['bold' => true]]];
    }
}
