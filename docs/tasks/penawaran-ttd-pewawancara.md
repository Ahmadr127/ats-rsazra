# Penawaran: Upload Surat & Salary oleh Admin, TTD oleh Pewawancara

Status: **ANALISA / RENCANA — menunggu keputusan alur**
Terakhir update: 2026-11-19

> Sesuai instruksi, dokumen ini **tidak mengambil inisiatif mengubah alur**. Bagian "Keputusan yang dibutuhkan" harus dijawab dulu sebelum implementasi.

## Permintaan (apa yang disampaikan)

1. Pada pipeline tahap **Penawaran / `surat_penawaran`**, admin meng-**upload surat penawaran dan salary**.
2. **Pewawancara** menerima email berisi:
   - tautan ke form **upload ulang surat yang sudah di-TTD** jika menerima;
   - tautan **tolak** jika menolak.

## Analisa kondisi saat ini

### 1. Data & skema

- `app/Models/OfferingLetter.php`
  - Fillable: `application_id`, `jabatan_ditawarkan`, `gaji`, `tanggal_mulai`, `catatan`, `sent_at`, `status`, `responded_at`, `rejection_reason`.
  - Cast: `tanggal_mulai` date, `sent_at`/`responded_at` datetime, `status` => `OfferingLetterStatus`.
  - Helper: `isPending()`, `isResponded()`.
  - **Belum ada kolom path file** untuk surat penawaran, lampiran salary, maupun surat hasil TTD.
- Migrasi terkait:
  - `2026_05_17_043041_create_offering_letters_table.php`
  - `2026_05_20_065206_add_response_columns_to_offering_letters_table.php`
- `app/Enums/OfferingLetterStatus.php`: `pending`, `accepted`, `rejected`; label masih **"Diterima Kandidat" / "Ditolak Kandidat"**.
- `gaji` saat ini adalah **text biasa** (`string`, maks. 100 di request), bukan file.

### 2. Alur pengiriman yang berjalan sekarang

- `app/Http/Controllers/OfferingLetterController.php::send()`
  - Permission `offering.manage`; cek `application->vacancy_id`, keberadaan stage `surat_penawaran`, status stage harus `isAdvanceable()`, dan `sent_at` masih kosong.
  - `updateOrCreate` offering dengan jabatan, gaji (text), tanggal mulai, catatan, `status = pending`.
  - Membuat **dua signed URL relatif** berlaku 7 hari: `offering.accept` dan `offering.reject`.
  - Mengirim email **ke kandidat** via `EmailNotificationService::dispatch('surat_penawaran', $candidate->email, ...)`, payload berisi `link_terima` dan `link_tolak`.
  - Jika email gagal → error, `sent_at` tidak diisi.
- `routes/web.php` (public, group middleware `signed.relative` + `throttle:signed-access`):
  - `GET/POST /penawaran/{offering}/terima` → `OfferingResponseController::showAcceptForm` / `accept`.
  - `GET/POST /penawaran/{offering}/tolak` → `OfferingResponseController::showRejectForm` / `reject`.
- `app/Http/Middleware/ValidateSignedUrl.php` memvalidasi signature terhadap path+query relatif, dengan fallback absolute lama dan normalisasi `&amp;signature=` dari email client.

### 3. Alur respon yang berjalan sekarang

- `app/Http/Controllers/OfferingResponseController.php`
  - `showAcceptForm()` / `showRejectForm()`: jika `isResponded()` → view `offering.already-responded`.
  - `accept()`: set status `accepted`, `responded_at = now()`, lalu `ApplicationPipelineService::advance($application)`; kirim notifikasi **database** `PenawaranDirespon` ke `User::withPermission(Permissions::OFFERING_MANAGE)`; view `offering.accepted`.
  - `reject()`: validasi `rejection_reason` opsional (maks. 1000), set status `rejected`, `responded_at`, `rejection_reason`, stage `surat_penawaran` → `Gagal`; notifikasi database yang sama; view `offering.rejected`.
  - **Tidak ada upload file** di kedua jalur.
- View: `resources/views/offering/accept-form.blade.php`, `reject-form.blade.php`, `accepted.blade.php`, `rejected.blade.php`, `already-responded.blade.php` (semua `x-layouts.public`).
- `app/Notifications/PenawaranDirespon.php`: `via()` hanya `['database']`, payload berisi status & nama kandidat.

### 4. Email

- `app/Services/EmailNotificationService.php::dispatch($key, $toEmail, $payload)`: mengambil `EmailTemplate` by key, render placeholder `{...}`, lalu `Mail::to(...)->queue(new TemplatedMail(...))`.
- `database/seeders/EmailTemplateSeeder.php`:
  - `surat_penawaran` → dikirim ke **kandidat**, placeholder `{nama_kandidat}`, `{judul_lowongan}`, `{jabatan_ditawarkan}`, `{gaji}`, `{tanggal_mulai}`, `{link_terima}`, `{link_tolak}`.
  - `tindakan_diperlukan` → template internal untuk "Kepala Unit, Manajer HR, Direktur", placeholder `{nama_penerima}`, `{judul_lowongan}`. **Template ini ada tetapi belum dipakai untuk email keluar** (semua `dispatch()` saat ini menuju email kandidat).
- Tidak ada template email khusus pewawancara.

### 5. Siapa "pewawancara" di data

- `app/Models/ApplicationStage.php`: fillable `interviewer_id`, relasi `interviewer()`. Diisi `InterviewScheduleController` saat menjadwalkan wawancara.
- `app/Models/InterviewResult.php`: fillable `interviewer_id`, relasi `interviewer()`. Diisi `InterviewController::decide()` dengan `auth()->user()->id` untuk semua stage wawancara.
- `app/Support/InterviewStageMap.php`: tiga stage `wawancara_user`, `wawancara_manajer_hr`, `wawancara_direktur`, masing-masing punya permission `interview.decide-*`.
- Konsekuensi: satu application bisa punya **lebih dari satu pewawancara** (user / manajer HR / direktur). Sumber paling lengkap adalah `interview_results.interviewer_id`; `application_stages.interviewer_id` hanya diisi saat penjadwalan.
- Belum ada aturan di codebase yang memilih satu pewawancara untuk keperluan penawaran.

### 6. RBAC

- `Permissions::OFFERING_MANAGE = 'offering.manage'` — role default hanya **HR Admin**.
- Pewawancara memiliki `interview.decide-user` / `-manager` / `-director`, **bukan** `offering.manage`.
- Karena itulah tautan respon saat ini memakai **signed URL publik**, bukan route `auth`.

### 7. Pola upload file yang bisa dipakai sebagai referensi

- `app/Http/Controllers/McuController.php::store()`:
  - `StoreMcuResultRequest`: `dokumen` => `nullable|file|mimes:pdf|max:3072`.
  - `$request->file('dokumen')->store('mcu-documents', 'public')`.
  - Path disimpan di `mcu_results.dokumen_path`; jika transaksi gagal, file dihapus.
- `app/Http/Controllers/DiscTestController.php` / `MbtiTestController`: `test-proofs/{application_id}`, `Storage::disk('public')`, hapus file lama saat replace.
- `config/filesystems.php` disk `public` dan symlink `public/storage` sudah tersedia.

### 8. Pipeline

- `app/Services/ApplicationPipelineService.php::advance()`:
  - stage aktif → `Selesai`, stage `Pending` berikutnya → `Aktif`.
  - Jika next stage `mcu`, `onboarding`, atau stage wawancara → **tidak mengirim email transisi** (`$silentStages`).
  - `accept()` offering saat ini otomatis meng-advance ke stage berikutnya (biasanya MCU).
- `reject()` offering tidak memanggil `fail()` service; langsung meng-update stage ke `Gagal`.

### 9. Test yang sudah ada

- `tests/Feature/OfferingResponseTest.php`: GET/POST accept & reject via signed URL, idempotensi, notifikasi `PenawaranDirespon`, view already-responded.
- `tests/Feature/OfferingMcuOnboardingTest.php`: HR admin lihat & kirim offering, validasi field, dual-send dicegah, lalu MCU & onboarding.
- `tests/Feature/NotificationDispatchTest.php`: pola pengecekan email/notification.

## Keputusan yang dibutuhkan (belum saya ambil)

Tanpa jawaban ini saya tidak akan mengubah alur:

| No | Pertanyaan | Opsi / catatan |
|---|---|---|
| 1 | Email dikirim ke **siapa**? | (a) hanya pewawancara, (b) pewawancara + kandidat, (c) kandidat tetap seperti sekarang dan pewawancara ditambah. |
| 2 | Pewawancara yang dipilih jika ada beberapa? | (a) semua `interview_results.interviewer_id` unik, (b) interviewer terakhir, (c) hanya stage `wawancara_user`, (d) ditentukan manual di panel offering. |
| 3 | "Salary" yang diupload admin itu **file** atau tetap field `gaji` text? | Jika file: format (PDF?), satu/multiple, ukuran maks, dan apakah `gaji` text tetap diisi. |
| 4 | Admin upload surat & salary **kapan**? | (a) bersamaan dengan tombol Kirim, (b) upload dulu terpisah baru kirim, (c) keduanya opsional. |
| 5 | Form terima oleh pewawancara: apakah **wajib** mengupload surat sudah di-TTD agar status jadi `accepted` dan pipeline lanjut? | (a) wajib, (b) opsional, (c) upload di langkah terpisah setelah terima. |
| 6 | Form tolak oleh pewawancara: tetap pakai `rejection_reason` opsional seperti sekarang, atau wajib? | Perlu diputuskan. |
| 7 | Tautan kandidat (`offering.accept` / `offering.reject`) yang lama **dihapus / dinonaktifkan** atau tetap aktif? | Jika kandidat tidak lagi menerima email, link lama masih bisa dipakai siapa pun yang punya URL. |
| 8 | Masa berlaku signed URL untuk pewawancara | Usulan mengikuti pola existing 7 hari, tapi perlu konfirmasi. |
| 9 | Status & notifikasi: label `accepted`/`rejected` masih "Diterima/Ditolak **Kandidat**" — diubah atau tidak? | Notifikasi `PenawaranDirespon` saat ini hanya ke HR Admin via database; perlu diputuskan apakah pewawancara/kandidat ikut dapat notifikasi. |
| 10 | Template email baru atau pakai `tindakan_diperlukan`? | `tindakan_diperlukan` placeholder-nya belum cocok untuk link TTD/tolak; kemungkinan besar perlu template baru. |

## Cakupan perubahan yang akan direncanakan setelah keputusan

(belum diimplementasikan — hanya peta area yang akan tersentuh)

- **Migrasi**: kolom path file di `offering_letters` (surat penawaran, lampiran salary, surat hasil TTD) + rollback.
- **Model & enum**: `OfferingLetter` fillable/cast/helper; kemungkinan label `OfferingLetterStatus` dan status baru jika diperlukan.
- **Request**: validasi upload admin (`SendOfferingLetterRequest` diperluas atau request baru) dan validasi upload TTD pewawancara.
- **Controller**: `OfferingLetterController::send()` (penerima email & payload), `OfferingResponseController` (form/upload terima & tolak untuk pewawancara).
- **Service/notification**: pembuatan signed URL, `EmailTemplateSeeder` (template baru), `PenawaranDirespon` atau notifikasi baru.
- **View**: `_aksi-surat-penawaran.blade.php` (field upload admin + preview file), view public baru/ubah untuk form TTD & tolak.
- **Route**: kemungkinan route public baru dengan `signed.relative` + `throttle:signed-access`.
- **Storage**: pola `public` disk + cleanup file saat penggantian/gagal.
- **RBAC**: kemungkinan tidak ada permission baru (signed URL), tapi perlu konfirmasi.
- **Test**: `OfferingResponseTest`, `OfferingMcuOnboardingTest`, `NotificationDispatchTest`, test upload file & email.
- **Dokumen**: XMI bila model berubah.

## Yang tidak akan diubah tanpa instruksi eksplisit

- Urutan/status pipeline di `ApplicationPipelineService`.
- Makna `pending`/`accepted`/`rejected` dan perilaku `advance()`/`fail()` saat ini.
- Tautan kandidat lama dan template email `surat_penawaran` yang sudah ada.
- Mapping permission `offering.manage` dan `interview.decide-*`.

## Checklist

- [ ] User menjawab 10 keputusan di atas
- [ ] Plan final ditulis ulang sesuai keputusan
- [ ] Implementasi (migrasi, model, request, controller, view, email, notifikasi)
- [ ] Test feature & regresi
- [ ] Pint, npm build, migrate/seed, XMI
- [ ] Commit
