# Tes DISC/MBTI Eksternal + Upload Bukti

Status: **SELESAI — terverifikasi**
Terakhir update: 2026-11-19

## Tujuan

Mengganti tes DISC/MBTI internal berbasis soal menjadi alur tes eksternal:

1. Pipeline mengisi link tes eksternal dan catatan.
2. Email tetap dikirim ke kandidat.
3. Kandidat mengerjakan tes di link eksternal.
4. Kandidat mengunggah satu atau lebih gambar bukti tes.
5. Setelah bukti diunggah, pipeline otomatis lanjut ke tahap berikutnya.
6. Seluruh soal, jawaban, hasil, dan scoring internal DISC/MBTI dihapus.

## Keputusan final user

| No | Keputusan |
|---|---|
| 1 | Email dikirim saat staff menekan **Simpan & Kirim Email**, bukan otomatis saat stage entry. |
| 2 | Email berisi tombol menuju halaman upload bukti. Form file tidak ditempel langsung di body email. |
| 3 | Bukti wajib ada. Namun karena poin 8, kandidat tidak menunggu staff klik Lanjutkan. |
| 4 | Yang boleh mengisi link dan mengirim email: **Admin HR** lewat permission baru. |
| 5 | Catatan dari pipeline ikut tampil di email kandidat. |
| 6 | Data lama DISC/MBTI dihapus karena masih development. |
| 7 | Bukti boleh lebih dari satu gambar. |
| 8 | Setelah kandidat upload bukti, pipeline **otomatis lanjut** ke tahap berikutnya. Staff masih bisa menolak sebelum kandidat upload. |

## Aturan upload bukti

- Minimal 1 gambar, maksimal 5 gambar dalam satu kali submit.
- Format: JPG, JPEG, PNG, WEBP.
- Maksimal 5 MB per gambar.
- Semua gambar diunggah sekaligus; setelah submit, pipeline langsung auto-advance.
- Kandidat tidak bisa upload setelah stage selesai/gagal.
- Jika upload ulang terjadi saat stage masih aktif, set bukti baru menggantikan set lama dan file lama dihapus.
- File disimpan di disk `public` dengan nama acak, misalnya `test-proofs/{application_id}/...`.
- Path gambar disimpan sebagai array JSON di submission.

## Alur target

### 1. Kandidat masuk tahap DISC/MBTI

- `ApplicationPipelineService::advance()` tetap membuat `disc_submissions` / `mbti_submissions` + token.
- **Tidak mengirim email.**
- Tetap `return` lebih awal agar email `transisi_tahap` tidak terkirim.
- Pipeline menampilkan status **“Link tes belum dikirim”**.

### 2. Pipeline mengisi link dan catatan

Form di panel tahap DISC/MBTI:

- `link_tes` — wajib, URL http/https, maks. 2048 karakter.
- `catatan` — opsional, teks maks. 2000 karakter.
- Tombol **Simpan & Kirim Email**.
- Setelah terkirim: tampilkan link, catatan, waktu kirim, status bukti, dan tombol **Kirim Ulang**.
- Link/catatan hanya bisa diubah selama stage masih aktif dan bukti belum diunggah.
- Jika kandidat sudah upload bukti, stage sudah auto-advance; form menjadi read-only.

### 3. Email ke kandidat

Tetap memakai `EmailNotificationService` + `TemplatedMail` sehingga logo Azra dan warna hijau `#007774` otomatis dipakai.

Template baru (jangan ubah `tes_tersedia` karena dipakai tes kompetensi):

- `tes_disc_tersedia`
- `tes_mbti_tersedia`

Placeholder:

- `{nama_kandidat}`
- `{judul_lowongan}`
- `{link_tes}`
- `{link_upload}`
- `{catatan}`

Catatan teknis: `EmailNotificationService::render()` membiarkan placeholder yang tidak dikirim. Payload wajib selalu mengirim `catatan` meskipun string kosong.

### 4. Halaman kandidat

Route lama tetap dipakai agar link lama tidak rusak:

- `GET /tes-disc/{token}` → `tes-disc.show`
- `POST /tes-disc/{token}` → `tes-disc.submit`
- `GET /tes-mbti/{token}` → `tes-mbti.show`
- `POST /tes-mbti/{token}` → `tes-mbti.submit`

Isi halaman:

- Jika stage sudah selesai dan bukti ada: tampilkan halaman sukses + daftar bukti.
- Jika stage gagal: tampilkan pesan tidak lolos.
- Jika stage aktif dan `link_tes` belum ada: tampilkan “Link tes belum tersedia, hubungi HR”.
- Jika stage aktif dan `link_tes` ada:
  - tombol **Buka Tes Eksternal**,
  - catatan HR,
  - form upload `proofs[]` multiple,
  - tombol **Kirim Bukti & Lanjut**.

Tidak ada soal, jawaban, scoring, atau hasil otomatis.

### 5. Auto-advance

Setelah semua gambar tersimpan:

1. Update `proof_paths` dan `proof_uploaded_at`.
2. Panggil `ApplicationPipelineService::advance($application)`.
3. Stage DISC/MBTI menjadi `selesai`; stage berikutnya aktif.
4. Jika tahap berikutnya DISC/MBTI lagi, submission baru dibuat tetapi email tidak dikirim otomatis.

Jika `advance()` gagal:

- bukti tetap tersimpan,
- tampilkan error bahwa pipeline gagal dilanjutkan,
- kandidat boleh upload ulang selama stage masih aktif.

### 6. Penolakan oleh staff

- Staff boleh menolak kandidat kapan saja selama stage DISC/MBTI masih aktif, termasuk sebelum kandidat upload.
- Tidak ada tombol **Lanjutkan** manual di tahap ini; kelanjutan terjadi otomatis setelah bukti.
- Route `lowongan.lamaran.lanjut` tetap dijaga: jika stage aktif DISC/MBTI dan bukti belum ada, tolak dengan error.

## Skema database

Karena data lama boleh dihapus, migration baru akan:

1. Drop tabel lama:
   - `disc_answers`
   - `disc_results`
   - `disc_question_words`
   - `disc_questions`
   - `mbti_answers`
   - `mbti_results`
   - `mbti_questions`
   - `disc_submissions`
   - `mbti_submissions`
2. Membuat ulang `disc_submissions` dan `mbti_submissions`:

```text
id
application_id  FK, unique, cascade delete
token           unique
link_tes        text nullable
catatan         text nullable
sent_at         timestamp nullable
proof_paths     json nullable
proof_uploaded_at timestamp nullable
timestamps
```

3. `down()` merecreate skema lama agar rollback tetap valid.

Catatan: data lama DISC/MBTI akan hilang permanen. Tidak ada migrasi hasil lama.

## Perubahan per layer

### Model

- Update `app/Models/DiscSubmission.php`
  - fillable: `application_id`, `token`, `link_tes`, `catatan`, `sent_at`, `proof_paths`, `proof_uploaded_at`
  - cast: `sent_at` datetime, `proof_paths` array, `proof_uploaded_at` datetime
  - hapus `answers()`, `result()`, `isSubmitted()`
  - tambah `hasProof()`, `isSent()`
- Update `app/Models/MbtiSubmission.php` dengan pola yang sama.
- Hapus:
  - `DiscQuestion`, `DiscQuestionWord`, `DiscAnswer`, `DiscResult`
  - `MbtiQuestion`, `MbtiAnswer`, `MbtiResult`
- `Application` tetap punya `discSubmission()` dan `mbtiSubmission()`.

### Controller dan request

- Rewrite `app/Http/Controllers/DiscTestController.php`
  - `show()`: instruksi + link eksternal + form upload.
  - `submit()`: validasi multiple images, simpan, update proof, auto-advance.
- Rewrite `app/Http/Controllers/MbtiTestController.php` dengan pola yang sama.
- Hapus `DiscQuestionController`, `MbtiQuestionController`.
- Hapus `SubmitDiscTestRequest`, `SubmitMbtiTestRequest`.
- Tambah request baru:
  - `SendExternalTestRequest` (link + catatan)
  - `UploadTestProofRequest` (array gambar)
- Tambah aksi kirim link di `ApplicationPipelineController` atau controller khusus:
  - `POST lowongan/{lowongan}/lamaran/{application}/tes-disc/kirim`
  - `POST lowongan/{lowongan}/lamaran/{application}/tes-mbti/kirim`
- Aksi kirim wajib cek permission `test.send-link`.

### Service

- `ApplicationPipelineService::advance()`
  - saat next stage `tes_disc` / `tes_mbti`: buat submission + token, **tanpa email**, lalu return.
  - guard: manual advance dari stage DISC/MBTI tanpa `proof_uploaded_at` ditolak.
- Hapus `DiscScoringService`, `MbtiScoringService`.
- Auto-advance setelah upload memanggil service yang sama.

### Route

Hapus:

- `Route::resource('soal-mbti', ...)`
- `Route::resource('soal-disc', ...)`
- import `DiscQuestionController`, `MbtiQuestionController`

Tambahkan route kirim link dan pertahankan route publik tes dengan throttle:

- `token-access` untuk GET
- `public-submit` untuk POST upload

### UI pipeline

- `resources/views/vacancies/partials/_aksi-tes-disc.blade.php`
  - form link + catatan + kirim email
  - status terkirim
  - daftar bukti
  - tombol Tolak selama stage aktif
  - tanpa tombol Lanjutkan manual
- `_aksi-tes-mbti.blade.php`: pola yang sama.
- `_detail-tahap.blade.php`
  - hapus skor DISC/MBTI
  - tampilkan link, catatan, waktu kirim, status bukti, daftar gambar.
- `_informasi-kandidat.blade.php`
  - hapus section hasil DISC/MBTI.
- `vacancies/pdf/kandidat.blade.php`
  - hapus section hasil DISC/MBTI.
- `exports/candidate-profile.blade.php`
  - hapus section hasil DISC/MBTI.
- `BuildCandidateProfileData.php`
  - hapus eager load `discSubmission.result`, `mbtiSubmission.result`.
- `VacancyPipelineController`
  - hapus eager load `.result`
  - mapping `isUserPic` untuk `tes_disc`/`tes_mbti` memakai permission `test.send-link`.
- `resources/views/components/layouts/app.blade.php`
  - hapus menu **Soal MBTI** dan **Soal DiSC**.
- Hapus view:
  - `resources/views/disc/`
  - `resources/views/mbti/`
  - `resources/views/disc-questions/`
  - `resources/views/mbti-questions/`
- Tambah/ubah view halaman kandidat sesuai halaman upload.

### RBAC

Hapus dari `app/Support/Permissions.php`:

- `MENU_MBTI_QUESTIONS`
- `MENU_DISC_QUESTIONS`
- `MBTI_QUESTION_VIEW/CREATE/UPDATE/DELETE`
- `DISC_QUESTION_VIEW/CREATE/UPDATE/DELETE`

Tambah:

- `TEST_SEND_LINK = 'test.send-link'`
- label: “Kirim link tes eksternal DISC/MBTI”
- group: “Tes”
- default role: `hr_admin`

`PermissionSeeder` sudah otomatis menghapus permission lama yang tidak ada di katalog. Jalankan ulang seeder setelah perubahan.

### Email

- `database/seeders/EmailTemplateSeeder.php`
  - tambah `tes_disc_tersedia`
  - tambah `tes_mbti_tersedia`
  - jangan ubah isi `tes_tersedia`
- Email memakai layout `resources/views/emails/templated.blade.php` yang sudah berlogo dan berwarna `#007774`.

### Seeder, factory, demo

- `DatabaseSeeder`: hapus `DiscQuestionSeeder`, `MbtiQuestionSeeder`.
- Hapus `DiscQuestionSeeder`, `MbtiQuestionSeeder`.
- Hapus factory soal/answer/result:
  - `DiscQuestionFactory`, `DiscQuestionWordFactory`, `DiscAnswerFactory`, `DiscResultFactory`
  - `MbtiQuestionFactory`, `MbtiAnswerFactory`, `MbtiResultFactory`
- Update `DiscSubmissionFactory`, `MbtiSubmissionFactory`:
  - hapus `started_at`/`submitted_at`
  - tambah state `sent()` dan `withProof()`
- Update `DemoPipelineCaseSeeder`:
  - seed link, catatan, sent_at, proof_paths untuk kasus DISC/MBTI
  - hapus `DiscResult`/`MbtiResult`
  - cleanup tidak lagi memanggil `result()->delete()`

### Tes

Hapus:

- `DiscQuestionCrudTest`
- `MbtiQuestionCrudTest`
- `DiscTestSubmissionTest`
- `MbtiTestSubmissionTest`
- `DiscScoringServiceTest`
- `MbtiScoringServiceTest`

Tambah/update tes:

- advance ke DISC/MBTI membuat submission tetapi tidak mengirim email.
- simpan link + catatan mengirim email dengan `link_tes`, `link_upload`, `catatan`.
- validasi URL dan permission `test.send-link`.
- kandidat bisa upload 1–5 gambar.
- upload menolak file bukan gambar, lebih dari 5, atau lebih dari 5 MB.
- upload menyimpan `proof_paths` dan `proof_uploaded_at`.
- upload otomatis memindahkan stage ke tahap berikutnya.
- upload tidak bisa dilakukan setelah stage selesai.
- manual advance tanpa bukti ditolak.
- staff bisa menolak sebelum bukti.
- route/menu/permission soal DISC/MBTI sudah tidak ada.
- update `NotificationDispatchTest`, `CandidatePdfExportTest`, dan tes lain yang menyebut DISC/MBTI.

### Cleanup

- Hapus referensi model DISC/MBTI yang dihapus di `build-class-xmi.php` jika script masih dipakai.
- `php artisan view:clear` dan `php artisan optimize:clear` setelah menghapus view/route.
- Pastikan `AppServiceProvider` audit list tetap memuat `DiscSubmission` dan `MbtiSubmission`.

## Checklist implementasi

### Fase 0 — Task
- [x] Buat file task ini
- [x] Konfirmasi tidak ada perubahan keputusan

### Fase 1 — Database dan model
- [x] Migration drop tabel lama + recreate submissions
- [x] Update `DiscSubmission` dan `MbtiSubmission`
- [x] Hapus model soal/answer/result
- [x] Update factory submission
- [x] Hapus factory soal/answer/result

### Fase 2 — Email
- [x] Tambah template `tes_disc_tersedia`
- [x] Tambah template `tes_mbti_tersedia`
- [x] Pastikan payload selalu mengirim `catatan`
- [x] Jalankan `EmailTemplateSeeder`

### Fase 3 — Pipeline
- [x] Ubah `ApplicationPipelineService::advance()`
- [x] Tambah guard manual advance
- [x] Tambah aksi simpan link + kirim email
- [x] Tambah permission `test.send-link`
- [x] Update `VacancyPipelineController` (PIC + eager load)
- [x] Update partial aksi DISC/MBTI

### Fase 4 — Halaman kandidat
- [x] Rewrite `DiscTestController`
- [x] Rewrite `MbtiTestController`
- [x] Tambah request validasi link dan upload
- [x] Implement multiple image upload
- [x] Implement auto-advance setelah upload
- [x] Hapus soal/scoring/result

### Fase 5 — UI dan ekspor
- [x] Update `_detail-tahap.blade.php`
- [x] Update `_informasi-kandidat.blade.php`
- [x] Update PDF kandidat
- [x] Update export profil
- [x] Hapus menu sidebar soal
- [x] Hapus view soal DISC/MBTI

### Fase 6 — RBAC dan cleanup
- [x] Hapus permission soal dari `Permissions.php`
- [x] Hapus route resource soal
- [x] Hapus controller/request soal
- [x] Hapus seeder soal
- [x] Update `DatabaseSeeder`
- [x] Update `DemoPipelineCaseSeeder`

### Fase 7 — Verifikasi
- [x] Hapus tes lama
- [x] Tambah tes alur baru
- [x] Update tes yang terdampak
- [x] `vendor/bin/pint --dirty --format agent`
- [x] `php artisan test --compact`
- [x] `npm run build`
- [x] Cek manual: pipeline isi link → email → upload beberapa gambar → auto-advance

### Catatan verifikasi (2026-11-19)

- Migration `2026_11_19_000000_replace_disc_mbti_with_external_tests` dijalankan di database development.
- `PermissionSeeder` dan `EmailTemplateSeeder` dijalankan ulang.
- Tes fitur eksternal: `ExternalTestFlowTest` 16 tes lulus (66 assertion).
- Tes terdampak: `NotificationDispatchTest` dan `CandidatePdfExportTest` lulus.
- Full suite: 615 lulus, 4 gagal. Semuanya tidak terkait DISC/MBTI: 1 `PasswordChangeTest` (middleware sengaja dihapus pada commit `615cccf`), 2 assertion `CandidateStatusPageTest` yang masih memakai teks lama `Tidak Lolos` (view sekarang `Tidak lolos`), dan 1 `CompetencyTestEngineTest` yang flaky pada batas detik serta lulus saat dijalankan terpisah.
- `npm run build` lulus; `ats-class-diagram.xmi` diregenerasi dari `build-class-xmi.php`.


## Di luar scope

- Tidak ada input manual tipe/hasil DISC/MBTI oleh staff.
- Tidak mengubah tes kompetensi.
- Tidak mengubah stage `tes_disc`/`tes_mbti` di workflow.
- Tidak mengubah branding email yang sudah ada.
- Tidak menambah reminder otomatis untuk link yang belum dikirim.
- Tidak menambah tampilan bukti di PDF/export kecuali diminta terpisah.
