# Panggil Kembali Kandidat Bertunda (Lanjutkan / Ulang)

Status: **RENCANA — menunggu keputusan alur**
Terakhir update: 2026-11-19

> Analisa dulu, belum implementasi. Alur "lanjutkan" menyentuh data lamaran lama, jadi perlu disetujui sebelum kode ditulis.

## Permintaan

1. Kandidat dengan **status tunda (Reserved)** pada penilaian wawancara bisa ikut fitur **Panggil Kembali** yang sudah ada.
2. Saat dipanggil, HR bisa memilih apakah kandidat **melanjutkan** proses lamaran lamanya atau **mengulang** dari awal.
3. Skala penilaian wawancara sudah diubah ke **1–3** (terpisah, sudah dikerjakan).

## Analisa kondisi saat ini

### Asal status tunda

- `app/Http/Requests/StoreInterviewResultRequest.php`: `keputusan` menerima `lulus|gagal|reserved`.
- `app/Http/Controllers/InterviewController.php::decide()`: untuk `reserved` memanggil `ApplicationPipelineService::reserve($application)`.
- `app/Services/ApplicationPipelineService.php::reserve()`: hanya mengubah **stage aktif** menjadi `ApplicationStageStatus::Reserved`, tanpa membuat lamaran baru dan tanpa mengubah `vacancy_id`.
- `ApplicationStageStatus::isAdvanceable()` bernilai true untuk `Aktif` **dan** `Reserved`, sehingga tombol aksi masih bisa dipakai.
- `app/Console/Commands/AutoRejectReservedKandidat.php`: pada lowongan lewat tenggat, semua kandidat `Reserved` otomatis di-`fail()` → berubah menjadi `Gagal`.

### Fitur Panggil Kembali yang ada

- `app/Services/CallbackCandidateFinder.php`
  - `gagalApplications()` hanya mengambil lamaran yang punya stage **`Gagal`**, dari lowongan lain dengan `job_template_id` sama, kecuali lowongan target.
  - `forVacancy()` menghasilkan `failed_stage_label` dari stage `Gagal` pertama.
  - `eligibleCandidateIds()` memakai query terluas (termasuk kegagalan skrining) untuk validasi POST.
  - Hard exclude: sudah onboarding (hired), sudah melamar di lowongan target tanpa undangan.
  - `active_elsewhere` ditandakan tapi **bukan** hard exclude; kandidat dengan lamaran aktif/reserved di lowongan lain tetap boleh diundang.
- `app/Http/Controllers/CallbackController.php::invite()`
  - Validasi `candidate_ids` terhadap `eligibleCandidateIds()`.
  - `callbackInvites()->updateOrCreate(...)`.
  - Email `undangan_panggil_kembali` ke tiap kandidat dengan payload `{nama_kandidat}`, `{judul_lowongan}`, `{link_lamar}` → `route('karier.lamar', $lowongan)` (selalu lamaran baru).
- `resources/views/callback/index.blade.php`: tabel dengan checkbox per kandidat, kolom "Gagal di tahap", toggle filter skrining, tombol Kirim Undangan.
- `database/seeders/EmailTemplateSeeder.php`: template `undangan_panggil_kembali` berisi `{link_lamar}`.
- `tests/Feature/CallbackInviteTest.php`: cakupan filter skrining, eksklusi hired/self-applied, dan pengiriman email.

### Konsekuensi

- Kandidat `Reserved` **belum muncul** di daftar panggil kembali sebelum auto-reject berjalan. Setelah auto-reject, statusnya jadi `Gagal` dan baru muncul — tetapi riwayat "tunda"-nya hilang.
- Tidak ada mekanisme "lanjutkan" saat ini; semua undangan mengarah ke form lamaran baru.

## Usulan alur (perlu persetujuan)

### 1. Daftar kandidat

- `CallbackCandidateFinder` menambahkan lamaran dengan stage **`Reserved`** selain `Gagal`.
- Baris menyimpan status asal: `Gagal` atau `Reserved`, dengan label tahap yang sesuai (`failed_stage_label` diambil dari stage `Gagal` **atau** `Reserved` pertama).
- Toggle skrining tetap berlaku untuk keduanya.
- Hard exclude tetap sama (hired, self-applied). `active_elsewhere` tetap hanya badge.

### 2. Pilihan metode di halaman callback

- Untuk baris berstatus **`Reserved`**, tampilkan radio/select per kandidat:
  - **Lanjutkan** (default untuk baris Reserved) — memakai lamaran lama.
  - **Ulang dari awal** — perilaku saat ini.
- Untuk baris **`Gagal`**, hanya "Ulang dari awal" yang tersedia (tidak ada stage untuk dilanjutkan).
- Nama input usulan: `mode[{candidate_id}]` dengan nilai `lanjut|ulang`.

### 3. Proses undang

- **`ulang`**: persis seperti sekarang — buat/perbarui `callbackInvites`, kirim email dengan `{link_lamar}`.
- **`lanjut`** (dalam satu transaksi):
  1. Cari lamaran sumber: baris callback yang dipilih (stage `Reserved` di lowongan lama, job template sama).
  2. Pastikan kandidat belum punya lamaran di lowongan target (sudah ada hard exclude; cek ulang di write path).
  3. Update lamaran sumber: `vacancy_id` → lowongan target.
  4. Update stage `Reserved` tersebut menjadi `Aktif`; stage `Selesai` sebelumnya dibiarkan, stage `Pending` setelahnya dibiarkan.
  5. Buat/perbarui `callbackInvites` seperti biasa.
  6. Kirim email dengan tautan **status lamaran** memakai token lamaran (`route('karier.lamaran.status', $application->token)`), bukan form lamaran baru.

### 4. Email

- Usulan: template **baru** `undangan_panggil_kembali_lanjut` dengan placeholder `{nama_kandidat}`, `{judul_lowongan}`, `{link_status}`.
- Template lama `undangan_panggil_kembali` tidak diubah, sehingga alur "ulang" tetap kompatibel.
- `EmailTemplateSeeder` perlu dijalankan ulang.

### 5. Pengaman

- Hanya boleh "lanjut" jika lowongan sumber dan target memakai `job_template_id` sama (sudah menjadi syarat finder).
- Jika lebih dari satu lamaran `Reserved` untuk kandidat yang sama, pilih yang **paling baru** (atau sesuai keputusan Anda).
- Lamaran yang sudah di-auto-reject menjadi `Gagal`, sehingga tidak lagi mendapat opsi "lanjut".

## Pertanyaan yang perlu diputuskan

| No | Pertanyaan | Usulan |
|---|---|---|
| 1 | "Lanjutkan" memindahkan lamaran lama ke lowongan target, atau membuat lamaran baru dengan menyalin stage yang sudah lewat? | **Pindahkan lamaran lama** (lebih sedikit duplikasi data). |
| 2 | Template email baru atau memakai template lama dengan tautan dinamis? | **Template baru** agar teks "ajukan lamaran baru" tidak salah untuk mode lanjut. |
| 3 | Kandidat `Reserved` tampil di filter default atau di toggle terpisah? | **Tampil di default**, dengan badge "Ditangguhkan". |
| 4 | Jika ada beberapa lamaran `Reserved`, pilih yang mana? | **Yang terbaru** berdasarkan `created_at`. |
| 5 | Apakah stage tes/wawancara lama yang sudah punya submission tetap dipakai setelah dipindah? | Usulan **ya**, dengan catatan risiko snapshot tes lama. Perlu keputusan. |

## Cakupan perubahan setelah disetujui

- `app/Services/CallbackCandidateFinder.php`: query `Gagal` + `Reserved`, field status asal.
- `app/Http/Controllers/CallbackController.php`: validasi `mode`, cabang `lanjut`/`ulang`, transaksi, email.
- `resources/views/callback/index.blade.php`: radio per baris + badge status.
- `database/seeders/EmailTemplateSeeder.php`: template baru.
- `tests/Feature/CallbackInviteTest.php`: test tampil tunda, mode lanjut/ulang, validasi, email.
- `docs/tasks/panggil-kembali-tunda.md`: checklist final.

## Yang tidak akan diubah tanpa instruksi

- `ApplicationPipelineService::advance/fail/reserve`.
- Auto-reject reserved.
- Permission `vacancy.callback`.
- Alur lamaran publik `karier.lamar`.

## Checklist

- [ ] Keputusan 5 poin di atas
- [ ] Finder menyertakan Reserved
- [ ] UI pilihan lanjut/ulang
- [ ] Controller cabang transaksi + email
- [ ] Template email baru + seeder
- [ ] Test & regresi
- [ ] Commit
