# Import Soal dari Excel (+ Template)

Status: **RENCANA — menunggu persetujuan**
Terakhir update: 2026-11-19

## Analisa kondisi saat ini

- Library **`maatwebsite/excel` 3.1.69** sudah terpasang dan dipakai untuk ekspor kandidat (`CandidateListExport`, `CandidateExportController`). Tidak perlu dependency baru.
- Pola ekspor yang ada: class `App\Exports\*` mengimplementasikan concern (`FromQuery`, `WithHeadings`, `WithStyles`, `ShouldAutoSize`), dipanggil lewat `Excel::download()` di controller, dengan pengecekan `requirePermission`.
- Belum ada kode import sama sekali (tidak ada `App\Imports`, tidak ada route import).
- Bank soal dikelola di `QuestionBankTemplateController` dengan pola: satu template punya banyak `questions`, tiap soal punya `tipe` (`mc`/`essay`/`image`), `pertanyaan`, `gambar_path` (opsional), `nilai_poin`, `urutan`, dan opsional `question_options` untuk MC.
- `Route::resource('template-bank-soal', ...)` memakai wildcard `{templateBankSoal}`. **Route import harus didefinisikan sebelum resource** agar path `/template-bank-soal/import` tidak ditangkap sebagai detail template.
- Permission yang relevan sudah ada: `question-bank.create` (tidak perlu permission baru).
- Kolom `gambar_path` sudah ada dari fitur sebelumnya. **Sesuai permintaan, Excel tidak mengirim gambar**: semua soal hasil import dibuat dengan `gambar_path = null`, dan gambar bisa ditambahkan kemudian lewat halaman edit template.

## Tujuan

Menambah alur import soal massal dari file Excel yang:

1. Menyediakan **template Excel resmi** yang bisa diunduh dari aplikasi.
2. Membuat satu template bank soal baru dari banyak baris soal sekaligus.
3. Mendukung ketiga tipe soal: `mc`, `essay`, `image`.
4. Memvalidasi seluruh baris sebelum menyimpan (all-or-nothing) dan menampilkan error nominal baris.
5. Tidak mengirim/ mengharapkan gambar dari Excel.

## Keputusan yang diusulkan

| No | Topik | Usulan | Catatan |
|---|---|---|---|
| 1 | Mode import | **Buat template baru**, nama diisi di form | Paling aman; tidak menyentuh template yang sudah dipakai lowongan. Append ke template existing bisa jadi fitur lanjutan. |
| 2 | Alur | **Upload → preview → konfirmasi** (2 langkah) | User bisa cek hasil parse sebelum commit. Data preview disimpan di session dengan token. |
| 3 | Format file | **`.xlsx` saja** | Template dibuat `.xlsx`; mempersempit edge case. CSV/xls bisa ditambah kemudian. |
| 4 | Ukuran & jumlah | Maks. **2 MB**, maks. **500 soal** | Proteksi memori/waktu parse. |
| 5 | Kolom opsi | Tetap **Opsi A–D** | MC minimal 2 opsi, maksimal 4. Opsi harus terisi berurutan dari A tanpa celah. |
| 6 | Nilai `Tipe` | Dropdown **Pilihan Ganda / Esai / Unggah Gambar**; parser juga menerima `mc`/`essay`/`image` (case-insensitive) | User-friendly sekaligus tolerance untuk file hasil edit manual. |
| 7 | `Poin` | Integer **1–100** | Sama dengan validasi form bank soal. |
| 8 | `Kunci Jawaban` | Wajib untuk MC (`A`–`D`), harus menunjuk opsi yang terisi; untuk essay/image **diabaikan** | Menghindari error karena kolom tetap ada di semua baris. |
| 9 | Urutan soal | Mengikuti **urutan baris** (nomor urut tidak diambil dari Excel) | Menghindari duplikat/gap nomor urut. |
| 10 | Gambar | **Tidak ada kolom gambar** di template; hasil import selalu `gambar_path = null` | Sesuai instruksi; gambar ditambahkan via edit template. |
| 11 | Strategi error | Jika ada satu baris invalid, **tidak ada yang disimpan**; tampilkan daftar error "Baris N: ..." | Transaksi DB + validasi sebelum commit. |
| 12 | RBAC | `question-bank.create` | Tanpa permission/role baru. |

> Konfirmasi poin 1–5 diperlukan sebelum implementasi; sisanya bisa disesuaikan.

## Format template Excel

File: **`template-import-soal.xlsx`**, tiga sheet:

### Sheet 1 — `Soal`

Header baris 1 (bold, freeze pane, auto-filter):

| Tipe | Pertanyaan | Poin | Opsi A | Opsi B | Opsi C | Opsi D | Kunci Jawaban |
|---|---|---|---|---|---|---|---|

- Baris 2 ke bawah dikosongkan (User mengisi sendiri).
- Dropdown data validation:
  - Kolom A (`Tipe`): `Pilihan Ganda`, `Esai`, `Unggah Gambar`.
  - Kolom H (`Kunci Jawaban`): `A`, `B`, `C`, `D`, `-` (kosong untuk non-MC).
- Kolom C (`Poin`) diberi validasi integer 1–100 bila memungkinkan.
- Lebar kolom diatur agar pertanyaan terbaca.

### Sheet 2 — `Contoh`

Header sama + 3 baris contoh:

1. MC 4 opsi dengan kunci `B`.
2. Essay tanpa opsi/kunci.
3. Unggah Gambar tanpa opsi/kunci.

### Sheet 3 — `Petunjuk`

- Cara isi: satu soal per baris, jangan mengubah header, jangan ada baris kosong di tengah.
- Tipe yang diterima + padanan kode (`mc`, `essay`, `image`).
- Aturan MC: minimal 2 opsi, maksimal 4, opsi berurutan dari A, kunci A–D harus menunjuk opsi terisi.
- Aturan essay/image: Opsi A–D dan Kunci dikosongkan.
- **Gambar tidak dikirim lewat Excel.** Setelah import, buka template → Edit untuk menambahkan gambar per soal.
- Batas: 2 MB, 500 soal.

## Alur aplikasi

### 1. Halaman index

- Tombol **Import Soal** (hanya untuk pemegang `question-bank.create`) di `question-bank-templates/index.blade.php`, di samping tombol "Buat Template".

### 2. Form import

`GET /template-bank-soal/import`:

- Input **Nama Template** (required, unique, maks. 255).
- Input file **File Excel** (required, `xlsx`, maks. 2 MB).
- Link **Unduh Template Excel**.
- Catatan singkat tentang gambar (tidak dikirim dari Excel).

### 3. Preview

`POST /template-bank-soal/import` → `preview()`:

1. Validasi request (nama + file).
2. Baca Excel dengan `Excel::toCollection(new QuestionImportReader, $file)`:
   - concern: `ToCollection`, `WithHeadingRow`, `SkipsEmptyRows`.
   - reader hanya mengambil baris pada sheet pertama (`Soal`).
3. `QuestionImportService::parse($rows)` menghasilkan `['questions' => [...], 'errors' => [...]]`.
4. Jika ada error: kembali ke form dengan daftar error bernomor baris, **tanpa menyimpan apa pun**.
5. Jika valid: simpan payload ke session (`question_import.{token}` berisi nama + questions), redirect ke halaman preview.
6. Halaman preview menampilkan tabel: nomor, tipe, pertanyaan, poin, ringkasan opsi, kunci; tombol **Simpan Template** dan **Batal** (Batal menghapus session).

### 4. Konfirmasi

`POST /template-bank-soal/import/confirm` → `store()`:

1. Ambil payload dari session berdasarkan token; jika tidak ada/expired → redirect ke form dengan pesan error.
2. `DB::transaction`: buat `QuestionBankTemplate`, lalu `questions` dengan `urutan = index + 1`, `gambar_path = null`, dan `question_options` untuk MC (`is_correct` sesuai kunci).
3. Hapus session, `Log::info` dengan `LogContext`, redirect ke index dengan pesan sukses.

### 5. Tambah gambar setelah import

- User membuka template hasil import → **Edit**.
- Form edit yang sudah ada (dari fitur sebelumnya) dipakai untuk menempelkan gambar ke soal yang diinginkan. Tidak ada UI/route baru untuk ini.

## Perubahan per lapisan

### Route (`routes/web.php`)

Didefinisikan **sebelum** `Route::resource('template-bank-soal', ...)`:

```php
Route::get('/template-bank-soal/import', [QuestionBankTemplateImportController::class, 'create'])->name('template-bank-soal.import.create');
Route::get('/template-bank-soal/import/template', [QuestionBankTemplateImportController::class, 'template'])->name('template-bank-soal.import.template');
Route::post('/template-bank-soal/import', [QuestionBankTemplateImportController::class, 'preview'])->name('template-bank-soal.import.preview');
Route::post('/template-bank-soal/import/confirm', [QuestionBankTemplateImportController::class, 'store'])->name('template-bank-soal.import.store');
```

### Controller & Request

- `app/Http/Controllers/QuestionBankTemplateImportController.php` (baru): `create`, `template`, `preview`, `store`, `cancel`.
- `app/Http/Requests/ImportQuestionBankRequest.php` (baru):
  - `nama` => `required|string|max:255|unique:question_bank_templates,nama`.
  - `file` => `required|file|mimes:xlsx|max:2048`.
  - Pesan Bahasa Indonesia.

### Import / Export / Service

- `app/Imports/QuestionImportReader.php`: `ToCollection`, `WithHeadingRow`, `SkipsEmptyRows`.
- `app/Exports/QuestionImportTemplateExport.php`: `WithMultipleSheets` → `QuestionImportSheet`, `QuestionImportExampleSheet`, `QuestionImportGuideSheet`.
  - Dropdown dibuat via `WithEvents` + `AfterSheet` memakai `PhpOffice\PhpSpreadsheet\Cell\DataValidation` (concern `WithDataValidation` **tidak tersedia** di versi 3.1.69 yang terpasang).
- `app/Services/QuestionImportService.php`:
  - `parse(Collection $rows): array` — normalisasi tipe, validasi per baris, kumpulkan error "Baris N: ...".
  - `create(array $payload): QuestionBankTemplate` — transaksi DB + log.

### View

- `resources/views/question-bank-templates/index.blade.php`: tombol Import Soal.
- `resources/views/question-bank-templates/import-create.blade.php`: form + link template + daftar error.
- `resources/views/question-bank-templates/import-preview.blade.php`: tabel preview + form konfirmasi token.

### Tidak berubah

- Enum `QuestionType`, model `Question`/`QuestionOption`, snapshot, tes kandidat, penilaian, RBAC.
- Tidak ada migrasi baru.

## Validasi baris (dikerjakan `QuestionImportService`)

- `Tipe` wajib; harus salah satu dari label/code yang dikenal (case-insensitive).
- `Pertanyaan` wajib, string, maks. 2000 karakter.
- `Poin` wajib, integer 1–100.
- MC:
  - Opsi A–B wajib; C/D opsional.
  - Tidak boleh ada celah: jika C kosong tapi D terisi → error.
  - `Kunci Jawaban` wajib A–D dan harus menunjuk opsi yang terisi.
- Essay/Image: opsi dan kunci diabaikan (tidak error).
- Baris kosong dilewati (`SkipsEmptyRows`); jika setelah difilter tidak ada soal → error "File tidak berisi soal".
- Jumlah soal > 500 → error.
- Nama template duplikat → error dari FormRequest.

## Rencana test

File baru `tests/Feature/QuestionImportExcelTest.php` dengan `Storage::fake('public')` tidak diperlukan (file import dibuat di memory via `UploadedFile::fake()`/`Excel::store()`), gunakan `Excel::store()` untuk membuat file uji di disk fake:

1. Template dapat diunduh: status 200, content-type spreadsheet, mengandung header "Tipe", "Pertanyaan", "Poin", "Opsi A", "Kunci Jawaban", dan 3 sheet.
2. Preview file valid menampilkan semua baris dan **tidak** membuat template/soal di DB.
3. Konfirmasi membuat template + 3 soal (mc/essay/image), urutan 1..3, opsi MC lengkap, `is_correct` sesuai kunci.
4. Soal hasil import memiliki `gambar_path = null` untuk semua tipe.
5. MC dengan hanya 2 opsi diterima; 1 opsi ditolak; opsi bercelah (A, C) ditolak.
6. Kunci `C` saat opsi C kosong ditolak; kunci di luar A–D ditolak.
7. Tipe tidak dikenal ditolak dengan nomor baris.
8. Poin 0, 101, atau bukan angka ditolak.
9. Pertanyaan kosong ditolak.
10. File tanpa baris soal ditolak.
11. Ekstensi file salah / bukan xlsx ditolak.
12. File > 2 MB ditolak.
13. Lebih dari 500 baris ditolak.
14. Nama template duplikat ditolak.
15. Konfirmasi tanpa session/token → redirect ke form dengan error.
16. Batal menghapus session.
17. Jika satu baris error, tidak ada template/soal tersimpan (atomic).
18. User tanpa `question-bank.create` mendapat 403 pada semua route import.
19. Setelah import, user bisa membuka edit template dan menambah gambar ke soal (regresi fitur sebelumnya).
20. Log import tercatat (opsional).

Regresi yang dijalankan: `QuestionImageTest`, `CompetencyTestEngineTest`, dan `RbacTest`.

## Urutan implementasi

1. Export template (3 sheet + dropdown + contoh + petunjuk).
2. Request + import reader + service parse/validate.
3. Controller + routes + view form & preview.
4. Service create + session confirm + logging.
5. Test feature + perbaikan.
6. Pint, npm build (jika ada perubahan Blade), jalankan suite.
7. Checklist dokumen + commit.

## Rollback

- Tidak ada migrasi; revert commit cukup.
- Template Excel yang sudah diunduh user tetap bisa dipakai di aplikasi lama (tanpa fitur import).
- Data template hasil import yang sudah tersimpan tidak otomatis dihapus; hapus manual bila perlu.

## Risiko / catatan

- Session preview expire/default 120 menit; token harus tidak bisa dipakai ulang setelah dikonfirmasi (session dihapus).
- `UploadedFile::fake()` untuk xlsx sebaiknya dibuat dengan `Excel::store(new QuestionImportTemplateExport, 'uji.xlsx', 'fake')` agar strukturnya benar-benar valid.
- Dropdown Excel bukan pengganti validasi server; tetap validasi penuh di `QuestionImportService`.
- Excel dapat berisi rumus; `ToCollection` membaca nilai hasil hitung, bukan rumus mentah — tetap batasi panjang teks.
- Import tidak menyentuh snapshot karena template baru belum dipakai lowongan mana pun.

## Checklist

- [ ] Analisa/disetujui
- [ ] Export template 3 sheet + dropdown + contoh + petunjuk
- [ ] FormRequest import (nama + file xlsx)
- [ ] Import reader + service parse/validasi baris
- [ ] Controller create/template/preview/store/cancel + routes sebelum resource
- [ ] View index button, form import, preview
- [ ] Session token + confirm + logging
- [ ] 20 test feature lulus
- [ ] Regresi QuestionImageTest, CompetencyTestEngineTest, RbacTest lulus
- [ ] Pint/npm build/suite
- [ ] Commit
