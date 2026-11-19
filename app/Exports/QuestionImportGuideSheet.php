<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionImportGuideSheet implements FromArray, ShouldAutoSize, WithStyles
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['PETUNJUK IMPORT SOAL'],
            [''],
            ['1. Isi soal pada sheet "Soal", satu soal per baris. Jangan mengubah header pada baris pertama.'],
            ['2. Jangan menyisipkan baris kosong di tengah data.'],
            ['3. Tipe yang diterima: Pilihan Ganda (mc), Esai (essay), Unggah Gambar (image).'],
            ['4. Untuk Pilihan Ganda: isi minimal Opsi A dan B, maksimal Opsi D, tanpa celah kosong.'],
            ['5. Kunci Jawaban wajib diisi A, B, C, atau D dan harus menunjuk opsi yang terisi.'],
            ['6. Untuk Esai dan Unggah Gambar: kosongkan Opsi A–D dan isi Kunci Jawaban dengan "-".'],
            ['7. Poin harus berupa angka bulat antara 1 sampai 100.'],
            ['8. Urutan soal mengikuti urutan baris.'],
            ['9. GAMBAR TIDAK DIKIRIM LEWAT EXCEL. Setelah soal masuk ke form, tambahkan gambar lewat kolom gambar di form, lalu simpan.'],
            ['10. Batas ukuran file 2 MB dan maksimal 500 soal.'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
