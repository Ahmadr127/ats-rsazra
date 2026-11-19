# Penawaran: Upload Surat & Salary oleh Admin, TTD oleh Pelamar

Status: **ANALISA / RENCANA — menunggu keputusan detail**
Terakhir update: 2026-11-19

> **Scope: hanya tahap `surat_penawaran`.** Tidak menyentuh MCU, onboarding, wawancara, atau logika umum pipeline.

## Permintaan

1. Di pipeline tahap **Penawaran**, admin meng-upload **surat penawaran** dan **salary**.
2. Email dikirim ke **pelamar**, berisi dua tautan:
   - tautan ke form **upload ulang surat yang sudah di-TTD** jika menerima;
   - tautan **tolak** jika menolak.

## Analisa kondisi saat ini

### 1. Data & skema

- `app/Models/OfferingLetter.php`
  - Fillable: `application_id`, `jabatan_ditawarkan`, `gaji`, `tanggal_mulai`, `catatan`, `sent_at`, `status`, `responded_at`, `rejection_reason`.
  - Cast: `tanggal_mulai` date, `sent_at`/`responded_at` datetime, `status` => `OfferingLetterStatus`.
  - Helper `isPending()` / `isResponded()`.
  - **Belum ada kolom path file** untuk surat penawaran, lampiran salary, atau surat hasil TTD.
- Migrasi: `2026_05_17_043041_create_offering_letters_table.php`, `2026_05_20_065206_add_response_columns_to_offering_letters_table.php`.
- `gaji` saat ini adalah **text biasa** (`string`, maks. 100 karakter di `SendOfferingLetterRequest`), bukan file.
- `app/Enums/OfferingLetterStatus.php`: `pending`, `accepted`, `rejected`; label sudah "Diterima/Ditolak **Kandidat**" sehingga tidak perlu diubah untuk alur ini.

### 2. Alur pengiriman yang berjalan sekarang

- `app/Http/Controllers/OfferingLetterController.php::send()`
  - Permission `offering.manage`; cek kecocokan vacancy, keberadaan stage `surat_penawaran`, status stage `isAdvanceable()`, dan `sent_at` masih kosong.
  - `updateOrCreate` offering: jabatan, gaji (text), tanggal mulai, catatan, `status = pending`.
  - Membuat dua signed URL relatif 7 hari: `offering.accept` dan `offering.reject`.
  - Mengirim email ke **`$application->candidate->email`** lewat `EmailNotificationService::dispatch('surat_penawaran', ...)`, payload: `nama_kandidat`, `judul_lowongan`, `jabatan_ditawarkan`, `gaji`, `tanggal_mulai`, `link_terima`, `link_tolak`.
  - Jika email gagal → `sent_at` tidak diisi.
- `routes/web.php` group `signed.relative` + `throttle:signed-access`:
  - `GET/POST /penawaran/{offering}/terima` → `OfferingResponseController::showAcceptForm` / `accept`.
  - `GET/POST /penawaran/{offering}/tolak` → `showRejectForm` / `reject`.
- `app/Http/Middleware/ValidateSignedUrl.php`: validasi signature relatif + fallback absolute lama + normalisasi `&amp;signature=`.

### 3. Alur respon yang berjalan sekarang

- `app/Http/Controllers/OfferingResponseController.php`
  - `showAcceptForm()` / `showRejectForm()`: jika `isResponded()` → view `offering.already-responded`.
  - `accept()`: set `accepted` + `responded_at`, panggil `ApplicationPipelineService::advance()`, kirim notifikasi database `PenawaranDirespon` ke HR Admin, view `offering.accepted`.
  - `reject()`: validasi `rejection_reason` opsional (maks. 1000), set `rejected` + `responded_at` + alasan, stage → `Gagal`, notifikasi database yang sama, view `offering.rejected`.
  - **Tidak ada upload file** di kedua jalur.
- View public: `resources/views/offering/accept-form.blade.php`, `reject-form.blade.php`, `accepted.blade.php`, `rejected.blade.php`, `already-responded.blade.php`.
- `app/Notifications/PenawaranDirespon.php`: `via()` hanya `['database']`, dikirim ke `User::withPermission(Permissions::OFFERING_MANAGE)`.

### 4. Email & template

- `app/Services/EmailNotificationService.php::dispatch()`: ambil `EmailTemplate` by key, render placeholder `{...}`, `Mail::to($toEmail)->queue(new TemplatedMail(...))`.
- `database/seeders/EmailTemplateSeeder.php`: template `surat_penawaran` sudah ditujukan ke pelamar dengan placeholder `link_terima` dan `link_tolak`. Bisa diperluas dengan placeholder baru untuk tautan upload TTD, atau dibuat template baru — **perlu keputusan**.
- Semua `dispatch()` saat ini memang ditujukan ke email kandidat/pelamar, jadi tidak ada pola email internal yang perlu diikuti.

### 5. RBAC & storage

- `Permissions::OFFERING_MANAGE` hanya untuk HR Admin. Form admin berada di panel pipeline yang sudah ada.
- Form respon pelamar memakai signed URL publik (tidak perlu login), sama seperti sekarang.
- Pola upload file yang bisa dipakai sebagai referensi: `McuController::store()` + `StoreMcuResultRequest` (`mimes:pdf`, `max:3072`, `Storage::disk('public')->store('mcu-documents', 'public')`, hapus file saat rollback).

### 6. Pipeline

- `ApplicationPipelineService::advance()` mengubah stage aktif → `Selesai` dan stage berikutnya → `Aktif`.
- Perilaku **accept = advance**, **reject = stage Gagal** sudah ada dan akan **dipertahankan**; yang ditambahkan hanyalah file upload dan tautan/form baru.
- `mcu`, `onboarding`, dan stage wawancara termasuk `$silentStages` (tidak dapat email transisi) — tidak diubah.

### 7. Test yang sudah ada

- `tests/Feature/OfferingResponseTest.php`: GET/POST accept & reject via signed link, idempotensi, notifikasi, view already-responded.
- `tests/Feature/OfferingMcuOnboardingTest.php`: kirim offering, validasi, pencegahan double-send, lalu MCU & onboarding.
- `tests/Feature/NotificationDispatchTest.php`: pola verifikasi email/notification.

## Keputusan detail yang masih dibutuhkan

| No | Pertanyaan | Catatan |
|---|---|---|
| 1 | "Salary" yang di-upload admin adalah **file PDF** atau tetap field `gaji` text? | Jika file: satu/multiple, format, ukuran maks. |
| 2 | Surat penawaran & salary diupload **bersamaan tombol Kirim** atau diunggah terpisah dulu? | Keduanya wajib atau opsional? |
| 3 | Saat pelamar menekan terima, upload surat sudah di-TTD **wajib** agar status `accepted` + pipeline lanjut? | Jika wajib, form terima harus menolak submit tanpa file. |
| 4 | Form tolak: alasan penolakan tetap opsional atau diwajibkan? | Sekarang opsional, maks. 1000. |
| 5 | Tautan `link_terima` lama diganti ke form upload TTD, atau form lama tetap ada lalu upload menyusul? | Menentukan apakah route `offering.accept` diubah atau ditambah. |
| 6 | Masa berlaku signed URL untuk pelamar tetap **7 hari**? | Sekarang 7 hari. |
| 7 | Template `surat_penawaran` **dimodifikasi** (tambah placeholder tautan upload TTD) atau dibuat template baru? | Template lama dipakai alur saat ini. |
| 8 | Notifikasi `PenawaranDirespon` ke HR Admin tetap seperti sekarang, atau ditambah info file TTD? | Saat ini database-only. |
| 9 | Format & ukuran maks file surat penawaran, salary, dan TTD | Usulan awal: PDF, maks. 3 MB (mengikuti MCU). Perlu konfirmasi. |
| 10 | File lama dihapus saat offering dikirim ulang / TTD diupload ulang? | Pola DISC/MBTI menghapus file lama saat replace. |

## Cakupan perubahan yang akan direncanakan setelah keputusan

(belum diimplementasikan — peta area yang akan tersentuh)

- **Migrasi**: kolom path file di `offering_letters` (surat penawaran, lampiran salary, surat TTD) + rollback.
- **Model**: `OfferingLetter` fillable/cast/helper aksesor URL file.
- **Request**: perluasan `SendOfferingLetterRequest` untuk upload admin; request baru untuk upload TTD pelamar.
- **Controller**: `OfferingLetterController::send()` (simpan file + generate signed URL + email ke pelamar); `OfferingResponseController` (form terima dengan upload TTD, form tolak).
- **View**: `_aksi-surat-penawaran.blade.php` (field upload + preview/tautan file), `offering/accept-form.blade.php` (input file TTD), mungkin `reject-form.blade.php`.
- **Route**: kemungkinan route public baru dengan `signed.relative` + `throttle:signed-access` untuk upload TTD.
- **Email**: `EmailTemplateSeeder` + payload `EmailNotificationService`.
- **Storage**: disk `public`, path `offering-documents/{application_id}/...`, cleanup file gagal/replace.
- **Test**: `OfferingResponseTest`, `OfferingMcuOnboardingTest`, `NotificationDispatchTest`, test upload & email.
- **XMI**: bila model/offering berubah.

## Yang tidak akan diubah tanpa instruksi eksplisit

- Penerima email: **pelamar** (bukan pewawancara).
- Stage lain (MCU, onboarding, wawancara) dan `ApplicationPipelineService`.
- Permission `offering.manage` dan `interview.decide-*`.
- Makna `pending` / `accepted` / `rejected`.

## Checklist

- [ ] Keputusan detail di atas dijawab
- [ ] Plan final ditulis ulang
- [ ] Implementasi & test
- [ ] Pint, npm build, migrate/seed, XMI
- [ ] Commit
