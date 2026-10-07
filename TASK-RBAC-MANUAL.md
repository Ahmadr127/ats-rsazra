# Task: RBAC Manual (tanpa package) — satu tombol/sidemenu satu permission

> File ini adalah task tracker. Update berkala setiap fase selesai.
> Terakhir update: 2026-10-07 — SELESAI. Semua fase hijau; AGENTS.md diperbarui.

## Fase 5 — Role sebagai data (bukan enum) + CRUD
- [x] 5a — DB & model: tabel `roles` (key, label), `users.role` → `role_id` FK, `permission_role.role` → `role_id`; model `Role` (dengan konstanta key); relasi `User::role()`; factory `withRole(string $key)`
- [x] 5b — Seeder: `RoleSeeder` (5 role default), `PermissionSeeder` rewrite (tanpa enum), `DatabaseSeeder`, `DummyCandidateSeeder`, `TestCase`
- [x] 5c — UI: `RoleController` CRUD (index/create/edit/delete) + view + sidemenu + permission `menu.roles`, `role.view/create/update/delete`; matrix permission × role-record; guard anti-lockout berbasis permission (bukan nama role)
- [x] 5d — Sweep: hapus `Enums/Role.php`, `RoleMiddleware` + testnya, `hasRole/isHrAdmin`; `User::withPermission` via role_id; Blade null-safe; migrasi ~30 file test ke key string
- [x] 5e — Verifikasi: `RoleCrudTest`, update `RbacTest`, `pint`, full suite (33 file Feature + Unit, semua hijau), `AGENTS.md`

## Tujuan
- Ganti total hardcode role (`hasRole`, `isHrAdmin`, `Role::`, `Gate::authorize`, `@can`, Policies) menjadi `User::hasPermission('...')`.
- Satu tombol/sidemenu = satu permission, disimpan di tabel DB + seeder, bisa dikelola via UI matriks.

## Keputusan user (final)
1. Penyimpanan: **tabel DB + seeder** (`permissions`, `permission_role`).
2. UI kelola: **ya** — matriks permission × role (checkbox), route `pengaturan/hak-akses`.
3. Mekanisme cek: **ganti total ke `hasPermission`** — 11 file Policy dihapus.

## Aturan yang TIDAK BOLEH berubah (scope check dipertahankan sebagai kode eksplisit)
- Unit scope: `employee.unit_id === vacancy.unit_id` untuk role tanpa `vacancy.view-org`.
- `wawancara_user`: harus `interviewer_id === user.id` + unit sama.
- `account.update`: tidak boleh edit diri sendiri.
- `job-template.publish`: hanya saat status Active.
- `unit.delete`: hanya jika unit tanpa lowongan.
- Notifikasi (OfferingResponse, InterviewSchedule, console): penerima dicari via permission, bukan hardcode role.
- `RoleMiddleware` + testnya TIDAK dihapus (keputusan: biarkan, tetap passing).

## Katalog permission (69 key) + role default (H=hr_admin, M=hr_manager, U=unit_head, D=director, E=employee)

| Key | Label | Default |
|---|---|---|
| `menu.dashboard` | Sidemenu Beranda | H M U D E |
| `menu.employees` | Sidemenu Karyawan | H |
| `menu.units` | Sidemenu Unit | H |
| `menu.accounts` | Sidemenu Akun | H |
| `menu.workflow-templates` | Sidemenu Template Alur | H |
| `menu.job-templates` | Sidemenu Template Lowongan | H |
| `menu.vacancies` | Sidemenu Lowongan | H M U D E |
| `menu.question-bank` | Sidemenu Bank Soal | H |
| `menu.email-templates` | Sidemenu Template Email | H |
| `menu.interview-templates` | Sidemenu Template Wawancara | H |
| `menu.rbac` | Sidemenu Hak Akses | H |
| `dashboard.view-org` | Dasbor seluruh organisasi | H M D |
| `dashboard.view-unit` | Dasbor unit sendiri | U E |
| `account.view` / `account.create` / `account.update` | Akun: lihat/buat/ubah | H |
| `employee.view` / `employee.create` / `employee.update` / `employee.delete` | Karyawan (semua data) | H |
| `employee.view-self` | Profil sendiri | H E |
| `unit.view` / `unit.create` / `unit.update` / `unit.delete` | Unit | H |
| `workflow-template.view/create/update/delete` | Template alur | H |
| `job-template.view/create/update/delete/publish/test/interview-templates` | Template lowongan | H |
| `question-bank.view/create/update/delete` | Bank soal | H |
| `interview-template.view/create/update/delete` | Template wawancara | H |
| `email-template.view` / `email-template.update` | Template email | H |
| `vacancy.view` | Daftar lowongan | H M U D E |
| `vacancy.view-org` | Lowongan semua unit (tanpa ini = unit sendiri) | H M D |
| `vacancy.update` / `vacancy.delete` | Ubah/hapus lowongan | H |
| `vacancy.candidate-detail` | Pipeline + detail kandidat | H M U D E |
| `vacancy.interview-templates` | Template wawancara per lowongan | H |
| `vacancy.export` | Export list + PDF kandidat | H |
| `vacancy.callback` | Panggil kembali kandidat | H |
| `application.advance` / `application.fail` | Pipeline lanjut/gagal | H M U D |
| `screening.decide` | Keputusan skrining CV | H U E |
| `interview.schedule` / `interview.reschedule` | Jadwalkan/ubah jadwal wawancara | H M |
| `interview.decide-user` | Nilai wawancara user | U E |
| `interview.decide-manager` | Nilai wawancara manajer HR | M |
| `interview.decide-director` | Nilai wawancara direktur | D |
| `offering.manage` | Kirim surat penawaran | H |
| `mcu.schedule` / `mcu.decide` | Jadwalkan/putuskan MCU | H |
| `onboarding.invite` / `onboarding.complete` | Undang/selesaikan onboarding | H |
| `test.manage` / `test.review-essay` / `test.decide` | Kelola/ulas/putuskan tes | H |
| `rbac.manage` | Kelola hak akses | H |

Dihapus tanpa pengganti (dead code): `AccountPolicy::delete`, `VacancyPolicy::viewScreening`, `VacancyPolicy::viewInterview`.

Pengecualian super-admin (perilaku lama dipertahankan, ada test): `hr_admin` memegang semua
permission KECUALI `interview.decide-user/manager/director` — HrAdmin menjadwalkan wawancara,
tidak menilainya. `InterviewStageMap::stageKeyForDecider()` prioritas: manager → director → user.

## Fase eksekusi

- [x] Fase 0 — File task + desain dikunci
- [x] Fase 1 — Fondasi: migrasi, `Permission`, `Support\Permissions`, `Support\InterviewStageMap`, `User` methods, `PermissionSeeder`, `DatabaseSeeder`, `TestCase` seeding
- [x] Fase 2 — UI kelola: route + `RolePermissionController` + view matriks + item sidemenu + directive `@permission`
- [ ] Fase 3 — Ganti total 19 controller + 10 FormRequest + 8 Blade, hapus 11 Policy
- [ ] Fase 4 — Verifikasi: `RbacTest` baru, `pint`, full suite, update `AGENTS.md`

## Log progres
- 2026-10-07: inventarisasi selesai; desain dikunci.
- 2026-10-07: Fase 1+2 selesai (fondasi, 71 permission, UI matriks, `@permission`).
- 2026-10-07: Fase 3 berjalan — selesai: ApplicationPipeline, CvScreening (hardcode resolveStageKey dihapus), Account, Mcu, McuSchedule, OfferingLetter, Onboarding, TestReview, Interview (hardcode resolveStageKey dihapus), InterviewSchedule, Dashboard, Vacancy, VacancyPipeline.
- 2026-10-07: Fase 5 berjalan — role-enum dihapus; tabel `roles` + CRUD + matrix per record; `permission:` middleware; guard lockout tanpa nama role. Ditemukan & diperbaiki saat verifikasi: `@endunlesspermission` tidak ada (pakai `@permission`), CvScreening pertahankan error-bag, vacancy index tolak user tanpa unit (403).
