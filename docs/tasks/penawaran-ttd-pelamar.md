# Penawaran: Upload Surat & Salary oleh Admin, TTD oleh Pelamar

Status: **RENCANA FINAL — menunggu persetujuan implementasi**
Terakhir update: 2026-11-19

> **Scope: hanya tahap `surat_penawaran`.** Tidak menyentuh MCU, onboarding, wawancara, atau logika umum pipeline.

## Permintaan

1. Di pipeline tahap **Penawaran**, admin mengisi data penawaran — termasuk **gaji yang sudah ada sebagai field teks** — dan meng-upload **surat penawaran** dalam **satu form yang sama** dengan tombol Kirim. Tidak ada upload file salary terpisah.
2. Email dikirim ke **pelamar**, berisi tautan:
   - tautan **terima** membuka halaman yang menampilkan **catatan**, **gaji**, dan **file surat penawaran** yang di-upload admin, lalu di halaman tersebut ada **form upload surat yang sudah di-TTD**;
   - tautan **tolak** untuk menolak penawaran.

## Analisa kondisi saat ini

### 1. Data & skema

- `app/Models/OfferingLetter.php`
  - Fillable: `application_id`, `jabatan_ditawarkan`, `gaji`, `tanggal_mulai`, `catatan`, `sent_at`, `status`, `responded_at`, `rejection_reason`.
  - Cast: `tanggal_mulai` date, `sent_at`/`responded_at` datetime, `status` => `OfferingLetterStatus`.
  - Helper `isPending()` / `isResponded()`.
  - **Belum ada kolom path file** untuk surat penawaran maupun surat hasil TTD.
- Migrasi: `2026_05_17_043041_create_offering_letters_table.php`, `2026_05_20_065206_add_response_columns_to_offering_letters_table.php`.
- `gaji` saat ini adalah **text biasa** (`string`, maks. 100 karakter di `SendOfferingLetterRequest`) dan sudah tampil di form penawaran maupun email. Jadi "salary" yang dimaksud adalah data ini, bukan dokumen upload baru.
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

## Keputusan yang sudah diberikan

| No | Keputusan |
|---|---|
| 1 | Admin memakai **satu form**: data penawaran + upload surat penawaran + tombol Kirim. |
| 2 | Email ke pelamar berisi tautan ke halaman yang menampilkan **catatan, gaji, dan file surat penawaran**, plus form upload surat sudah di-TTD. Tautan tolak tetap ada. |
| 3 | Penerima email adalah **pelamar**, bukan pewawancara. |
| 4 | Salary tetap field `gaji` teks, bukan upload file. |
| 5 | "File surat lamaran" = **file surat penawaran yang di-upload admin**. |
| 6 | Upload surat sudah di-TTD di halaman terima **wajib**. |

## Detail teknis (default yang akan dipakai, bisa diubah)

| No | Topik | Default |
|---|---|---|
| 1 | Upload surat penawaran oleh admin | **Wajib**, satu form dengan tombol Kirim. |
| 2 | Form tolak | Alasan penolakan tetap **opsional**, maks. 1000 karakter (tidak diubah). |
| 3 | Masa berlaku signed URL | **7 hari**, mengikuti link yang ada sekarang. |
| 4 | Template email `surat_penawaran` | **Tidak diubah**: detail ditampilkan di halaman tautan, bukan di email. |
| 5 | Notifikasi `PenawaranDirespon` | Tetap ke HR Admin via database; payload ditambah info keberadaan file TTD. |
| 6 | Format & ukuran file | PDF, maks. **3 MB** per file (mengikuti pola `mcu-documents`). |
| 7 | Cleanup file | File lama dihapus saat penggantian/rollback (mengikuti pola DISC/MBTI). |
| 8 | Status & pipeline | `accepted` tetap memanggil `advance()`, `rejected` tetap menggagalkan stage. |

Alur sudah disepakati; detail teknis di atas adalah default implementasi, bukan perubahan alur.

## Cakupan perubahan yang akan direncanakan setelah keputusan

(belum diimplementasikan — peta area yang akan tersentuh)

- **Migrasi**: kolom path file di `offering_letters` (surat penawaran dan surat TTD) + rollback. Field `gaji` tetap apa adanya.
- **Model**: `OfferingLetter` fillable/cast/helper aksesor URL file.
- **Request**: perluasan `SendOfferingLetterRequest` untuk upload surat penawaran pada form yang sama; request baru untuk upload TTD pelamar.
- **Controller**: `OfferingLetterController::send()` (simpan file surat penawaran + generate signed URL + email ke pelamar); `OfferingResponseController::showAcceptForm()` (kirim data catatan, gaji, dan URL file surat penawaran ke view) serta `accept()` (simpan file TTD + status accepted + `advance()`); `reject()` tetap.
- **View**: `_aksi-surat-penawaran.blade.php` (satu form: field lama + input file surat penawaran); `offering/accept-form.blade.php` menampilkan catatan, gaji, tautan/download file surat penawaran, dan input file TTD; `reject-form.blade.php` tetap.
- **Route**: `offering.accept` GET/POST dipakai untuk halaman detail + upload TTD; kemungkinan tidak perlu route baru.
- **Email**: `EmailTemplateSeeder` + payload `EmailNotificationService`.
- **Storage**: disk `public`, path `offering-documents/{application_id}/...`, cleanup file gagal/replace.
- **Test**: `OfferingResponseTest`, `OfferingMcuOnboardingTest`, `NotificationDispatchTest`, test upload & email.
- **XMI**: bila model/offering berubah.

## Yang tidak akan diubah tanpa instruksi eksplisit

- Penerima email: **pelamar** (bukan pewawancara).
- Field `gaji` yang sudah ada (tidak diubah menjadi upload file).
- Stage lain (MCU, onboarding, wawancara) dan `ApplicationPipelineService`.
- Permission `offering.manage` dan `interview.decide-*`.
- Makna `pending` / `accepted` / `rejected`.

## Checklist

- [ ] Keputusan detail di atas dijawab
- [ ] Plan final ditulis ulang
- [ ] Implementasi & test
- [ ] Pint, npm build, migrate/seed, XMI
- [ ] Commit
