<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionImportGuideSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['PETUNJUK IMPORT SOAL'],
            [''],
            ['1. Isi soal pada sheet "Soal", satu soal per baris. Jangan mengubah header pada baris pertama.'],
            ['2. Baris 2–4 pada sheet "Soal" berisi contoh untuk tiap tipe (Pilihan Ganda, Esai, Unggah Gambar). Ganti atau hapus baris contoh sesuai kebutuhan.'],
            ['3. Jangan menyisipkan baris kosong di tengah data.'],
            ['4. Tipe yang diterima: Pilihan Ganda (mc), Esai (essay), Unggah Gambar (image).'],
            ['5. Untuk Pilihan Ganda: isi minimal Opsi A dan B, maksimal Opsi D, tanpa celah kosong.'],
            ['6. Kunci Jawaban wajib diisi A, B, C, atau D dan harus menunjuk opsi yang terisi.'],
            ['7. Untuk Esai dan Unggah Gambar: kosongkan Opsi A–D dan isi Kunci Jawaban dengan "-".'],
            ['8. Poin harus berupa angka bulat antara 1 sampai 100.'],
            ['9. Urutan soal mengikuti urutan baris.'],
            ['10. GAMBAR TIDAK DIKIRIM LEWAT EXCEL. Setelah soal masuk ke form, tambahkan gambar lewat kolom gambar di form, lalu simpan.'],
            ['11. Batas ukuran file 2 MB dan maksimal 500 soal.'],
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
