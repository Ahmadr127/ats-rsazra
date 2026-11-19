# Import Soal dari Excel ke Form Bank Soal (+ Template)

Status: **SELESAI — terverifikasi**
Terakhir update: 2026-11-19

## Analisa kondisi saat ini

- Library **`maatwebsite/excel` 3.1.69** sudah terpasang dan dipakai untuk ekspor kandidat (`CandidateListExport`, `CandidateExportController`). Tidak perlu dependency baru.
- Pola ekspor yang ada: class `App\Exports\*` mengimplementasikan concern (`FromQuery`, `WithHeadings`, `WithStyles`, `ShouldAutoSize`), dipanggil lewat `Excel::download()` di controller dengan `requirePermission`.
- Belum ada kode import sama sekali (tidak ada `App\Imports`, tidak ada route import).
- Form create bank soal (`question-bank-templates/create.blade.php` + `_question-form.blade.php`) memakai Alpine `templateForm(initialQuestions)`. Fungsi ini sudah bisa menerima array soal awal dan menormalkannya — tinggal memanfaatkannya untuk hasil import.
- `Route::resource('template-bank-soal', ...)` memakai wildcard `{templateBankSoal}`. **Route import harus didefinisikan sebelum resource** agar path `/import` tidak terbaca sebagai detail template.
- Permission yang relevan sudah ada: `question-bank.create` (tidak perlu permission baru).
- Sesuai permintaan: **Excel tidak mengirim gambar**. Hasil import mengisi form dengan `gambar_path = null`; gambar bisa ditambahkan user lewat file input yang sudah ada di form tersebut sebelum menyimpan.
- Tidak ada migrasi baru: skema `questions`/`question_options`/`gambar_path` sudah tersedia dari fitur sebelumnya.

## Tujuan

Menambah cara mengisi form create template bank soal dari file Excel:

1. User mengunduh **template Excel resmi** dari aplikasi.
2. User mengisi template dan mengunggahnya di halaman **Buat Template Bank Soal**.
3. Aplikasi mem-parse dan memvalidasi semua baris, lalu **mengisi otomatis daftar soal di form yang sudah ada**.
4. User bisa **edit ulang** (teks, tipe, poin, urutan, opsi, dan gambar) lalu menekan **Simpan Template** seperti biasa.
5. Mendukung `mc`, `essay`, `image`. Baris invalid ditolak sebelum form terisi.

## Keputusan final

| No | Topik | Keputusan | Catatan |
|---|---|---|---|
| 1 | Mode import | **Auto-fill form create yang sudah ada** | Tidak membuat template otomatis. User edit lalu simpan via `template-bank-soal.store`. |
| 2 | Preview | **Tidak ada halaman preview terpisah** | Form create itu sendiri menjadi preview & editor. |
| 3 | Format file | **`.xlsx` saja** | Template dan upload sama-sama xlsx. |
| 4 | Kolom opsi MC | **Opsi A–D** | Minimal 2 opsi, maksimal 4, harus berurutan dari A. |
| 5 | Ukuran & jumlah | Maks. **2 MB**, maks. **500 soal** | Proteksi memori/waktu parse. |
| 6 | Nilai `Tipe` | Dropdown **Pilihan Ganda / Esai / Unggah Gambar**; parser juga menerima `mc`/`essay`/`image` (case-insensitive) | User-friendly sekaligus toleran terhadap file hasil edit manual. |
| 7 | `Poin` | Integer **1–100** | Sama dengan validasi form bank soal. |
| 8 | `Kunci Jawaban` | Wajib untuk MC (`A`–`D`), harus menunjuk opsi terisi; untuk essay/image **diabaikan** | Kolom tetap ada di semua baris. |
| 9 | Urutan soal | Mengikuti **urutan baris** | Nomor urut tidak diambil dari Excel. |
| 10 | Gambar | **Tidak ada kolom gambar** di template; hasil import selalu tanpa gambar | Gambar ditambahkan lewat file input di form create/edit. |
| 11 | Strategi error | Jika ada satu baris invalid, **form tidak diisi** dan ditampilkan daftar error "Baris N: ..." | Tidak ada penulisan DB sampai user menekan Simpan. |
| 12 | RBAC | `question-bank.create` | Tanpa permission/role baru. |

## Format template Excel

File: **`template-import-soal.xlsx`**, tiga sheet:

### Sheet 1 — `Soal`

Header baris 1 (bold, freeze pane, auto-filter):

| Tipe | Pertanyaan | Poin | Opsi A | Opsi B | Opsi C | Opsi D | Kunci Jawaban |
|---|---|---|---|---|---|---|---|

- Baris 2 ke bawah dikosongkan.
- Dropdown data validation:
  - Kolom A (`Tipe`): `Pilihan Ganda`, `Esai`, `Unggah Gambar`.
  - Kolom H (`Kunci Jawaban`): `A`, `B`, `C`, `D`, `-`.
- Kolom C (`Poin`) diberi validasi integer 1–100 bila memungkinkan.

### Sheet 2 — `Contoh`

Header sama + 3 baris contoh: MC 4 opsi berkunci `B`, essay tanpa opsi/kunci, Unggah Gambar tanpa opsi/kunci.

### Sheet 3 — `Petunjuk`

- Satu soal per baris, jangan ubah header, jangan ada baris kosong di tengah.
- Tipe yang diterima + padanan kode (`mc`, `essay`, `image`).
- Aturan MC: minimal 2 opsi, maksimal 4, berurutan dari A, kunci A–D harus menunjuk opsi terisi.
- Aturan essay/image: Opsi A–D dan Kunci dikosongkan.
- **Gambar tidak dikirim lewat Excel.** Setelah form terisi, tambahkan gambar per soal langsung di form sebelum menyimpan.
- Batas: 2 MB, 500 soal.

## Alur aplikasi

### 1. Tombol unduh template

Di `question-bank-templates/create.blade.php` (dan opsional di `index.blade.php`) ada tautan **Unduh Template Excel** ke route `template-bank-soal.import.template`.

### 2. Unggah Excel di form create

Panel baru di halaman create, di atas daftar soal:

- Input file `.xlsx` + tombol **Isi dari Excel**.
- Tombol ini mengirim POST ke route `template-bank-soal.import.parse` (form terpisah dari form utama, atau tombol dengan `formaction` yang berbeda).
- Link unduh template dan catatan bahwa gambar tidak ikut dari Excel.

### 3. Parse & isi form

`POST /template-bank-soal/import.parse`:

1. Validasi: file wajib, `xlsx`, maks. 2 MB.
2. Baca dengan `Excel::toCollection(new QuestionImportReader, $file)`:
   - concern `ToCollection`, `WithHeadingRow`, `SkipsEmptyRows`; hanya sheet pertama (`Soal`).
3. `QuestionImportService::parse($rows)` → `['questions' => [...], 'errors' => [...]]`.
4. Jika ada error: redirect kembali ke create dengan `$importErrors` (flash session), form **tidak terisi**.
5. Jika valid: simpan ke session flash `question_import` berisi `questions` + `nama` yang sedang diketik, redirect ke `template-bank-soal.create`.
6. `QuestionBankTemplateController::create()` membaca flash tersebut dan mengirim `initialQuestions` + `importNama` ke view.
7. `create.blade.php` memanggil `templateForm(@js($initialQuestions))`; daftar soal langsung terisi, user bisa edit/reorder/tambah gambar, lalu menekan **Simpan Template** (endpoint lama, tidak diubah).

### 4. Simpan

Tidak ada logika baru. `QuestionBankTemplateController::store()` menerima `questions` seperti biasa, termasuk `gambar` dari file input yang ditambahkan user setelah import.

## Perubahan per lapisan

### Route (`routes/web.php`)

Didefinisikan **sebelum** `Route::resource('template-bank-soal', ...)`:

```php
Route::get('/template-bank-soal/import/template', [QuestionBankTemplateImportController::class, 'template'])->name('template-bank-soal.import.template');
Route::post('/template-bank-soal/import/parse', [QuestionBankTemplateImportController::class, 'parse'])->name('template-bank-soal.import.parse');
```

Tidak ada route preview/confirm/cancel/store baru.

### Controller & Request

- `app/Http/Controllers/QuestionBankTemplateImportController.php` (baru):
  - `template()`: `Excel::download(new QuestionImportTemplateExport, 'template-import-soal.xlsx')`.
  - `parse(ParseQuestionExcelRequest $request)`: parse + validasi + flash session + redirect.
  - Keduanya memanggil `requirePermission(Permissions::QUESTION_BANK_CREATE)`.
- `app/Http/Requests/ParseQuestionExcelRequest.php` (baru):
  - `file` => `required|file|mimes:xlsx|max:2048`, pesan Bahasa Indonesia.
- `QuestionBankTemplateController::create()`: baca `session('question_import')` dan kirim `initialQuestions`/`importNama` ke view.

### Import / Export / Service

- `app/Imports/QuestionImportReader.php`: `ToCollection`, `WithHeadingRow`, `SkipsEmptyRows`.
- `app/Exports/QuestionImportTemplateExport.php`: `WithMultipleSheets` → `QuestionImportSheet`, `QuestionImportExampleSheet`, `QuestionImportGuideSheet`.
  - Dropdown dibuat via `WithEvents` + `AfterSheet` memakai `PhpOffice\PhpSpreadsheet\Cell\DataValidation` (concern `WithDataValidation` **tidak tersedia** di versi 3.1.69).
- `app/Services/QuestionImportService.php`:
  - `parse(Collection $rows): array` — normalisasi tipe, validasi per baris, hasil `questions` siap dipakai `templateForm()` + `errors` bernomor baris.
  - Tidak perlu method `create()` karena penyimpanan tetap di `store()`.

### View

- `question-bank-templates/create.blade.php`:
  - panel unggah + link template + daftar `$importErrors`;
  - `templateForm(@js($initialQuestions ?? []))`;
  - `value="{{ old('nama', $importNama ?? '') }}"`.
- `question-bank-templates/_question-form.blade.php`:
  - kecil: `normalizeQuestion()` harus mengisi 4 opsi kosong bila `options` kosong/undefined, agar soal non-MC hasil import tetap aman saat dirender.
  - Tidak ada perubahan lain; file input gambar yang sudah ada langsung bisa dipakai untuk soal hasil import.
- `question-bank-templates/index.blade.php`: opsional tautan "Unduh Template" di samping "Buat Template".

### Tidak berubah

- Enum `QuestionType`, model `Question`/`QuestionOption`, snapshot, tes kandidat, penilaian, RBAC, `store()`/`update()`/`destroy()`.
- Tidak ada migrasi baru.

## Validasi baris (`QuestionImportService`)

- `Tipe` wajib; label/code dikenal (case-insensitive).
- `Pertanyaan` wajib, string, maks. 2000 karakter.
- `Poin` wajib, integer 1–100.
- MC: Opsi A–B wajib, C/D opsional, tanpa celah; `Kunci Jawaban` wajib A–D dan menunjuk opsi terisi.
- Essay/Image: opsi dan kunci diabaikan.
- Baris kosong dilewati; jika tidak ada soal → error "File tidak berisi soal".
- Jumlah soal > 500 → error.
- Error dikumpulkan semua, bukan berhenti di baris pertama.

## Rencana test

File baru `tests/Feature/QuestionImportExcelTest.php`. Buat file uji dengan `Excel::store(...)` ke disk fake agar benar-benar xlsx valid.

1. Template dapat diunduh: 200, content-type spreadsheet, header "Tipe"/"Pertanyaan"/"Poin"/"Opsi A"/"Kunci Jawaban", 3 sheet.
2. Parse file valid → redirect ke create; halaman create mengandung pertanyaan hasil import dan nama template terisi.
3. Setelah parse, **belum ada** baris `question_bank_templates`/`questions` di DB.
4. User menekan Simpan Template (POST store) dengan data hasil parse → template + soal + opsi + `is_correct` + urutan 1..3 tersimpan.
5. Semua soal hasil import punya `gambar_path = null`; user bisa menambahkan gambar lewat POST store sebelum simpan.
6. MC 2 opsi diterima; 1 opsi ditolak; opsi bercelah (A, C) ditolak.
7. Kunci `C` saat opsi C kosong ditolak; kunci di luar A–D ditolak.
8. Tipe tidak dikenal ditolak dengan nomor baris.
9. Poin 0/101/bukan angka ditolak; pertanyaan kosong ditolak.
10. File tanpa baris soal ditolak.
11. Ekstensi salah / bukan xlsx ditolak; file > 2 MB ditolak.
12. Lebih dari 500 baris ditolak.
13. Saat error, form create tidak terisi (tidak ada session `question_import`).
14. User tanpa `question-bank.create` mendapat 403 untuk template & parse.
15. Nama template duplikat tetap ditolak oleh `store()` (regresi).
16. Regresi: `QuestionImageTest`, `CompetencyTestEngineTest`, `RbacTest`.

Hasil verifikasi:

- `QuestionImportExcelTest`: 16 test / 75 assertion lulus.
- Regresi terpilih (import + gambar + engine + PDF + RBAC + eksternal): 86 test / 397 assertion lulus.
- Suite lengkap: 653 lulus, 3 gagal — ketiganya pre-existing dan tidak terkait (`PasswordChangeTest`, 2 assertion `CandidateStatusPageTest`).
- `pint --dirty` dan `npm run build` selesai.

## Urutan implementasi

1. Export template 3 sheet + dropdown + contoh + petunjuk.
2. `ParseQuestionExcelRequest` + `QuestionImportReader` + `QuestionImportService::parse`.
3. Controller `template()`/`parse()` + routes sebelum resource.
4. Update `create()` controller & `create.blade.php` + penyesuaian kecil `normalizeQuestion()`.
5. Test feature + perbaikan.
6. Pint, npm build, jalankan suite.
7. Checklist dokumen + commit.

## Rollback

- Tidak ada migrasi; revert commit cukup.
- File template yang sudah diunduh user tidak berubah.
- Template hasil import yang sudah disimpan user adalah data normal; tidak dihapus otomatis.

## Risiko / catatan

- Flash session `question_import` hanya untuk satu request; refresh halaman create setelah redirect tidak mengisi ulang form (sesuai perilaku flash). Jika ternyata diinginkan tetap terisi saat validasi `store()` gagal, gunakan `old()` yang sudah ada + kirim ulang `initialQuestions` dari `Session::pull()`.
- Payload `questions` harus berformat sama dengan `@js($template->questions->map(...))` di `edit.blade.php`: `id = null`, `tipe` value string, `pertanyaan`, `nilai_poin`, `correct_option`, `options` array.
- Dropdown Excel bukan pengganti validasi server; semua aturan tetap dicek di service.
- Import tidak menyentuh snapshot karena template baru belum dipakai lowongan.

## Checklist

- [x] Disetujui
- [x] Export template 3 sheet + dropdown + contoh + petunjuk
- [x] ParseRequest + reader + service parse/validasi baris
- [x] Controller template/parse + routes sebelum resource
- [x] Update controller & view create agar terisi otomatis + normalizeQuestion
- [x] Test feature lulus
- [x] Regresi lulus
- [x] Pint/npm build/suite
- [x] Commit
