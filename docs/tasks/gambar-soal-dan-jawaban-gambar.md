# Gambar pada Soal & Tipe Soal Jawaban Gambar

Status: **SELESAI — terverifikasi**
Terakhir update: 2026-11-19

## Tujuan

Menambah dua kemampuan pada tes kompetensi internal:

1. Setiap soal di template bank soal **boleh** memiliki **satu** gambar pendamping (opsional).
2. Ditambahkan tipe soal baru **`image`** yang jawabannya berupa **satu gambar yang diunggah kandidat**.

Keduanya mengikuti pola yang sudah ada: template bank soal → konfigurasi tes lowongan → snapshot → submission kandidat → ulasan staf. Tidak mengubah alur tes eksternal DISC/MBTI yang sudah selesai.

## Ruang lingkup

Termasuk:

- Kolom baru, migrasi + rollback.
- Enum tipe soal baru.
- Upload/hapus gambar soal di form create/edit template bank soal.
- Penyalinan gambar soal ke snapshot saat snapshot dibuat/config disimpan.
- Render gambar soal di halaman kandidat dan halaman ulasan.
- Upload gambar jawaban kandidat pada saat submit tes, dengan validasi.
- Penilaian manual (skor) untuk jawaban gambar, identik dengan esai.
- Tampilan ulasan staf, detail tahap, dan ekspor PDF.
- Penyesuaian RBAC label permission (tanpa menambah permission baru).
- Test feature baru dan pembaruan test yang ada.

Tidak termasuk:

- Mengubah scoring MC, alur pipeline, atau tes eksternal DISC/MBTI.
- Image processing/resizing (tidak ada library image processing di project).
- Penghapusan otomatis file saat `application` dihapus (perilaku yang sama dengan bukti tes eksternal saat ini).

## Keputusan final

| No | Topik | Keputusan | Catatan |
|---|---|---|---|
| 1 | Jumlah gambar soal | Maks. **1** per soal, opsional | Sesuai permintaan. |
| 2 | Gambar boleh untuk tipe apa saja | **Ya**, MC / esai / image | Gambar hanya sebagai ilustrasi soal. |
| 3 | Format gambar soal | JPG, JPEG, PNG, WEBP; maks. **5 MB** | Sama dengan aturan bukti tes (`UploadTestProofRequest`). |
| 4 | Lokasi gambar soal | Disk `public`, `question-images/{template_id}/{question_id}-{uuid}.{ext}` | Nama acak; satu folder per template memudahkan cleanup. |
| 5 | Tipe baru | `QuestionType::Image` dengan value `image`, label **Unggah Gambar** | Konsisten dengan `mc`/`essay` (value bahasa Inggris, label Bahasa Indonesia). |
| 6 | Jumlah gambar jawaban | **1** per soal | Sesuai permintaan ("candidate-uploaded image"). |
| 7 | Format gambar jawaban | JPG, JPEG, PNG, WEBP; maks. **5 MB** | Sama dengan gambar soal. |
| 8 | Wajib diisi? | **Wajib** saat kandidat menekan tombol Kirim | Jika waktu habis dan auto-submit, jawaban boleh kosong (perlakuan sama seperti esai kosong saat ini). |
| 9 | Lokasi gambar jawaban | Disk `public`, `test-answer-images/{application_id}/{question_id}-{uuid}.{ext}` | URL dipakai lewat `Storage::disk('public')->url(...)`. |
| 10 | Scoring jawaban gambar | **Manual**, sama seperti esai: `skor = null`, `is_reviewed = false`, `total_skor` ditunda sampai semua jawaban dinilai | Tidak ada automatic grading. |
| 11 | Permission | Pakai `test.review-essay` yang ada; **label** diubah menjadi "Nilai jawaban esai/gambar" | Key permission tidak berubah, tidak ada role/permission baru. |
| 12 | Ekspor PDF | Tampilkan ringkasan jawaban; untuk jawaban gambar hanya catatan **"Jawaban berupa gambar (tersimpan di sistem)"**, gambar **tidak** disisipkan | DomPDF bisa memuat base64, tetapi menyisipkan gambar upload berisiko membuat PDF besar. |
| 13 | Snapshot | Gambar soal **disalin** ke folder snapshot, bukan sekadar menyimpan path ke file sumber | Menjaga invariant: perubahan/hapusan soal tidak memengaruhi submission yang sedang berjalan. |
| 14 | Form upload soal | **UID stabil per soal + rename nama file input saat submit** | Paling minim error: tetap satu form, tanpa endpoint/state async, dan gambar mengikuti soal walau urutan digeser. |
| 15 | Cleanup | Gambar soal dihapus **langsung** saat soal/template dihapus; file snapshot **sengaja disimpan** | Submission lama tetap bisa menampilkan gambar. |

Poin 8, 12, 13, 14, dan 15 sudah dikonfirmasi. Belum ada keputusan yang terbuka.

## Aturan file

### Gambar soal

- Diunggah bersamaan dengan form create/update template bank soal (satu `POST`/`PUT`, multipart).
- Saat create: file disimpan setelah baris `questions` dibuat (butuh `question_id` untuk path).
- Saat update:
  - Ada file baru → simpan file baru, perbarui kolom, hapus file lama setelah transaksi berhasil.
  - Tidak ada file baru dan checkbox "Hapus gambar" tidak dicentang → pertahankan path lama.
  - Checkbox "Hapus gambar" dicentang → set kolom `null` dan hapus file lama setelah transaksi berhasil.
- Saat soal dihapus lewat update (tidak masuk daftar `question_ids`) → hapus file gambarnya.
- Saat template dihapus (`destroy`) → hapus seluruh folder `question-images/{template_id}`.
- Jika transaksi gagal setelah file tersimpan, hapus file yang baru disimpan (try/catch di controller).

### Gambar jawaban

- Diunggah kandidat bersama `POST /tes/{token}` (form diubah ke `multipart/form-data`).
- Disimpan hanya untuk soal bertipe `image`.
- File lama tidak ada (jawaban hanya dibuat sekali karena submit idempoten).
- Jika submission dihapus manual oleh staf di masa depan, cleanup bisa ditambahkan terpisah; tidak masuk scope ini.

### Snapshot

- `VacancyTestSnapshot::createFromVacancyTest()` menyalin `questions.gambar_path` ke file baru di `test-snapshot-images/{snapshot_id}/...` memakai `Storage::disk('public')->copy()` (atau `put(get(...))`).
- `vacancy_test_snapshot_questions.gambar_path` berisi path salinan tersebut.
- File snapshot **tidak dihapus** saat konfigurasi tes disimpan ulang, karena snapshot lama masih bisa direferensikan submission lama. `VacancyTestController::save()` sudah membuat snapshot baru tiap kali simpan; ini tidak diubah.
- Dengan salinan ini, gambar soal yang diubah/dihapus di template tidak memengaruhi snapshot yang sudah ada.

## Perubahan per lapisan

### 1. Migrasi

File baru: `database/migrations/2026_11_20_000000_add_gambar_to_questions_snapshot_questions_and_test_answers.php`

`up()`:

- `questions`: tambah `gambar_path` string nullable setelah `pertanyaan`.
- `vacancy_test_snapshot_questions`: tambah `gambar_path` string nullable setelah `pertanyaan`.
- `test_answers`: tambah `gambar_jawaban_path` string nullable setelah `jawaban_teks`.

`down()`: hapus ketiga kolom (path saja; file tidak dihapus otomatis — rollback data loss diterima).

### 2. Enum & Model

- `app/Enums/QuestionType.php`: tambah `case Image = 'image';` dan label `'Unggah Gambar'`.
- `app/Models/Question.php`: tambah `gambar_path` ke `$fillable`.
- `app/Models/VacancyTestSnapshotQuestion.php`: tambah `gambar_path` ke `$fillable`.
- `app/Models/TestAnswer.php`: tambah `gambar_jawaban_path` ke `$fillable`.
- `app/Models/VacancyTestSnapshot.php::createFromVacancyTest()`:
  - sertakan `'gambar_path' => $this->copyQuestionImage($question->gambar_path, $snapshot->id)`;
  - helper privat `copyQuestionImage(?string $path, int $snapshotId): ?string` memakai `Storage::disk('public')`.
- `database/factories/QuestionFactory.php`: tambah state `withImage(string $path)` untuk keperluan test (opsional tapi disarankan).

### 3. Request / validasi

Tidak membuat FormRequest baru untuk soal (pola saat ini memakai `$request->validate()` di controller). Tambahkan aturan:

```php
'questions.*.gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
'questions.*.gambar_lama' => ['nullable', 'string'],
'questions.*.hapus_gambar' => ['nullable', 'boolean'],
```

Pada `TestController::submit()`, bangun aturan dinamis hanya untuk soal snapshot bertipe `image`:

```php
"answers.{$question->id}" => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
```

dengan pesan Bahasa Indonesia. Path auto-submit saat waktu habis tetap memanggil `doSubmit($submission, [], [])` agar tidak kena validasi `required`.

### 4. Controller

- `QuestionBankTemplateController::store()/update()`:
  - terima file, simpan ke path yang ditentukan, isi `gambar_path`;
  - kumpulkan path lama yang akan dihapus, hapus **setelah** `DB::transaction` sukses;
  - `destroy()`: kumpulkan path/file gambar, hapus template, lalu `Storage::disk('public')->deleteDirectory("question-images/{$id}")`.
- `TestController`:
  - `submit()` validasi file dan kirim `$request->file('answers', [])` ke `doSubmit()`.
  - `doSubmit(TestSubmission $submission, array $answers, array $files = [])`.
  - Untuk `QuestionType::Image`: simpan file ke `test-answer-images/{application_id}/...`, tulis `gambar_jawaban_path`, `skor = null`, `is_reviewed = false`.
  - Ubah pengecekan `$hasEssay` menjadi `$hasManualReview` (essai **atau** image) agar `total_skor` tetap `null`.
- `TestReviewController`:
  - `scoreEssay()` diubah menjadi `scoreManual()` (route name/URI tetap `lowongan.tes.ulasan.skor`).
  - Guard ditoleransi untuk `Essay` dan `Image`; tetap menolak `Mc` (422).
  - Logika agregasi `total_skor` tidak berubah.

### 5. View

- `resources/views/question-bank-templates/create.blade.php` & `edit.blade.php`: tambah `enctype="multipart/form-data"` pada `<form>`; edit view mengirim `gambar_path` ke state Alpine untuk preview.
- `resources/views/question-bank-templates/_question-form.blade.php`:
  - tombol tipe ketiga "Unggah Gambar";
  - setiap question object diberi `uid` stabil (mis. `crypto.randomUUID()`), `x-for` memakai `:key="question.uid"` agar DOM/file input mengikuti soal saat di-reorder;
  - file input memakai `name="pending-file"` + `:data-uid="question.uid"` (file tidak bisa `x-model`);
  - saat `prepareSubmit()`, untuk tiap soal pada index final `qi`: cari input ber-`data-uid`, ubah `name` menjadi `questions[qi][gambar]`; untuk soal tanpa file pilih, set `disabled = true` agar tidak terkirim sebagai file kosong;
  - hidden `questions[qi][gambar_lama]` dan checkbox `questions[qi][hapus_gambar]` tetap dibuat di `prepareSubmit()`;
  - preview gambar yang sudah ada.
- `resources/views/test/show.blade.php`:
  - form tambah `enctype="multipart/form-data"`;
  - tampilkan gambar soal di bawah teks pertanyaan bila `gambar_path` ada;
  - cabang `image`: `<input type="file" name="answers[{{ $id }}]" accept="...">` plus teks bantuan format/ukuran.
  - Timer/auto-submit tidak diubah (native form submit tetap mengirim file yang sudah dipilih).
- `resources/views/vacancies/partials/_aksi-tes-kompetensi.blade.php`:
  - tampilkan gambar soal di bawah pertanyaan;
  - cabang non-MC: jika `image`, tampilkan thumbnail/link `gambar_jawaban_path`, selain itu `jawaban_teks`;
  - form penilaian (`skor`) tetap dipakai untuk kedua tipe.
- `resources/views/vacancies/partials/_detail-tahap.blade.php`:
  - tambah thumbnail gambar soal dan gambar jawaban pada ringkasan jawaban kandidat.
- `resources/views/exports/candidate-profile.blade.php`:
  - bagian "Hasil Tes Kompetensi" ditambah tabel ringkas per soal (pertanyaan, jawaban teks atau keterangan gambar, skor).
- `resources/views/job-template-test/show.blade.php` & `resources/views/vacancy-test/show.blade.php`:
  - badge tipe sudah memakai `tipe_label` dari enum, jadi otomatis; pastikan warna untuk `image` dibedakan (mis. ungu) pada bagian `:class`.
- `app/Support/Permissions.php`: ubah label `TEST_REVIEW_ESSAY` menjadi "Nilai jawaban esai/gambar". Jalankan ulang `PermissionSeeder` setelah deploy.

### 6. Keamanan & RBAC

- Upload gambar soal hanya lewat route `template-bank-soal.*` yang sudah dilindungi `question-bank.create/update`.
- Upload jawaban kandidat lewat route publik `tes.submit` dengan token + throttle `public-submit` (sudah ada). Tidak menambah route baru.
- Penilaian gambar memakai `test.review-essay` + pengecekan `vacancy_test_id` yang sudah ada di `TestReviewController`.
- Validasi `questions.*.id` dengan `Rule::exists(...)->where('question_bank_template_id', ...)` tetap menjaga tidak ada soal template lain yang bisa diubah.

## Rencana test

Tambahkan test di `tests/Feature/CompetencyTestEngineTest.php` (atau file baru `QuestionImageTest.php` untuk fokus upload) dengan `Storage::fake('public')` dan `UploadedFile::fake()->image()`:

1. HR admin bisa membuat soal bertipe `image` lewat template bank soal.
2. HR admin bisa membuat soal apa pun dengan gambar; path tersimpan dan file ada di storage.
3. Validasi menolak file bukan gambar dan file > 5 MB.
4. Update soal: gambar lama diganti dan file lama terhapus.
5. Update soal: checkbox hapus gambar membuat kolom `null` dan file terhapus.
6. Update template: soal yang dihapus ikut menghapus file gambarnya.
7. Hapus template: folder `question-images/{id}` terhapus.
8. Snapshot menyalin gambar soal ke `test-snapshot-images/{snapshot_id}` dan `vacancy_test_snapshot_questions.gambar_path` terisi.
9. Setelah template/source soal dihapus, gambar di snapshot masih bisa diakses (assert file exists pada path snapshot).
10. Halaman kandidat menampilkan input file untuk tipe `image` dan menampilkan `<img>` untuk gambar soal.
11. Submit dengan gambar valid → `test_answers.gambar_jawaban_path` terisi, `skor = null`, `is_reviewed = false`, `test_submission.total_skor = null`.
12. Submit tanpa gambar pada tipe `image` → validasi gagal (`assertSessionHasErrors('answers.X')`).
13. Submit file > 5 MB / bukan gambar → validasi gagal dan tidak ada file tersimpan.
14. Auto-submit saat waktu habis tetap membuat jawaban image dengan path `null` tanpa error.
15. Staf bisa menilai jawaban gambar; `total_skor` terhitung setelah semua jawaban review.
16. Staf tidak bisa menilai jawaban MC lewat route `skor` (422).
17. Halaman ulasan & detail tahap menampilkan tautan/thumbnail gambar jawaban.
18. Ekspor PDF kandidat dengan jawaban gambar tetap berhasil (status 200).
19. Tipe `image` tidak memerlukan opsi jawaban (create/update template sukses tanpa `options`).

Verifikasi manual tambahan (sulit di feature test): pilih gambar pada soal A, geser urutan A ke bawah, submit, pastikan gambar masih melekat ke soal A (bukan ke soal yang menggantikan posisinya).

Jalankan juga test yang sudah ada untuk regressions: `CompetencyTestEngineTest`, `CandidatePdfExportTest`, `RbacTest`, `ExternalTestFlowTest`, `NotificationDispatchTest` (pastikan alur eksternal tidak berubah).

Hasil verifikasi:

- `QuestionImageTest`: 18 test / 70 assertion lulus.
- Regressions terpilih: 63 test / 267 assertion lulus.
- Suite lengkap: 637 lulus, 3 gagal — ketiganya **pre-existing** dan tidak terkait (`PasswordChangeTest` middleware sengaja dihapus, 2 assertion `CandidateStatusPageTest` masih menulis `Tidak Lolos` padahal view merender `Tidak lolos`).
- `pint --dirty`, `npm run build`, `php artisan migrate`, `PermissionSeeder`, dan pembaruan XMI selesai.

## Urutan implementasi

1. Migrasi + enum + model + factory.
2. Controller & validasi question-bank + form Blade (upload/hapus gambar soal).
3. Snapshot copy + test freeze behavior.
4. Tipe `image`: form bank soal, konfigurasi tes, halaman kandidat, submit controller.
5. Ulasan staf, detail tahap, PDF, label permission.
6. Test feature + perbaikan.
7. `vendor/bin/pint --dirty --format agent`, `npm run build`, regenerate XMI bila diagram diubah, jalankan suite.
8. Update checklist dokumen ini, lalu commit.

## Rollback

- Code: revert commit.
- Migrasi: `php artisan migrate:rollback --step=1` (menghapus 3 kolom).
- File yang sudah terupload tetap ada di disk; hapus manual folder `question-images`, `test-snapshot-images`, `test-answer-images` bila ingin bersih total. **Jangan** menghapus folder snapshot sebelum memastikan tidak ada submission aktif yang memakainya.
- Permission label kembali ke tekstual lama lewat `PermissionSeeder` setelah code di-revert.

## Risiko / catatan

- Form kandidat sekarang multipart; pastikan `confirmSubmit()` Alpine tidak mencegah submit saat file sudah dipilih (logika saat ini hanya mencegah double-submit, aman).
- File input di dalam `<template x-for>` tidak bisa `x-model`; memakai `data-uid` + rename `name` di `prepareSubmit()`. Ini yang membuat file tetap mengikuti soal setelah reorder.
- `max:5120` adalah kilobyte (5 MB), sama seperti aturan bukti tes.
- DomPDF tidak menyisipkan gambar upload; bila nanti diinginkan, gunakan base64 data URI dan batasi ukuran.

## Checklist

- [x] Migrasi + rollback dibuat dan dijalankan di DB dev
- [x] Enum `Image`, model, factory diperbarui
- [x] Upload/hapus gambar soal di create/edit template
- [x] Cleanup file saat update & destroy template
- [x] Snapshot menyalin gambar & tahan terhadap perubahan source
- [x] Form kandidat multipart + render gambar soal + input file image
- [x] Validasi & penyimpanan gambar jawaban saat submit
- [x] `total_skor` ditunda untuk tipe `image`
- [x] Penilaian manual staf menerima image (permission label diperbarui)
- [x] Ulasan, detail tahap, dan PDF menampilkan informasi gambar
- [x] Test feature baru (18 test di atas) lulus
- [x] Regressions: CompetencyTestEngineTest, Pdf, Rbac, ExternalTestFlow, Notification lulus
- [x] Pint, npm build, XMI, seeder permission dijalankan
- [x] Dokumen checklist dicentang & commit
