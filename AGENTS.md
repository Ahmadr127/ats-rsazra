# AGENTS.md — ATS RS Azra (Laravel 12, PHP 8.3, PostgreSQL)

## Commands
- `php artisan test --compact` — full suite (SQLite `:memory:`, see `phpunit.xml`).
- Single file: `php artisan test --compact tests/Feature/CvScreeningTest.php`; single test: add `--filter=testName`.
- `vendor/bin/pint --dirty --format agent` — required after any PHP edit.
- `npm run build` (or `npm run dev`) — required after Blade/CSS/JS changes or Vite manifest errors.
- `composer run dev` — serve + queue + logs + vite together.
- `php artisan migrate --seed` — base seed (creates `admin` / HR Admin). Demo walkthrough: `php artisan db:seed --class=DummyCandidateSeeder` (logins `admin`, `kepala_unit`, `hr_manager`, `direktur`, `staff_demo`, password `password`).

## AuthN (Fortify, username-based)
- Login key is `username`, not email (`FortifyServiceProvider::authenticateUsing`). Inactive users (`is_active=false`) rejected at auth with validation error.
- `must_change_password` flow exists — account tests must set it or handle redirect.

## AuthZ — manual RBAC, no package (verified)
- `composer.lock` contains **no** `spatie/laravel-permission`, `silber/bouncer`, or similar. Do not introduce one.
- Roles are **data**: `roles` table (`key` immutable slug, `label`), full CRUD at `pengaturan/peran` (`RoleController`, permissions `menu.roles` + `role.view/create/update/delete`). `users.role_id` FK (nullable; role-less user = no permissions). Never hardcode role keys in access logic.
- Permissions are **data**: `permissions` table + `permission_role` pivot. Single source of truth is `App\Support\Permissions` (constants + `catalog()` with default role keys); `PermissionSeeder` rebuilds the catalog (idempotent). One button/sidemenu = one permission (~77 keys, see `TASK-RBAC-MANUAL.md`).
- Enforcement: `$user->requirePermission(Permissions::X)` in controllers (+ explicit scope checks, see below); `permission:xxx` route middleware only for the settings area (`hak-akses`, `peran`); `@permission('...')` in Blade (`@elsepermission`/`@endpermission` exist, `@unlesspermission` does NOT — no `@endunlesspermission`).
- `User::permissionKeys()` is request-cached; `User::flushPermissionCache()` is called on matrix/role/user-role changes (plus a `role_id`-dirty hook). No persistent cache.
- Lockout guards are role-agnostic: matrix update and role delete are rejected when no role would retain both `rbac.manage` + `menu.rbac`.
- `hr_admin` holds every permission **except** `interview.decide-user/manager/director` (schedules but never judges — pre-RBAC behavior, covered by test).

## Scope checks — keep alongside permission checks (not permissions themselves)
- Unit scope: user without `vacancy.view-org` must satisfy `$user->isInUnit($vacancy->unit_id)`; user without org access and without `employees` row is denied (403) on vacancy list/detail. Dashboard forces `filters['unit_id']` to own unit (`0` when unitless).
- `wawancara_user` decisions additionally require `interviewer_id === user.id`.
- `account.update` forbids editing self; `job-template.publish` requires Active status; `unit.delete`/`role.delete` blocked when in use.
- Stage→permission map lives in `App\Support\InterviewStageMap` (priority manager → director → user). Notification recipients follow permissions (`User::withPermission()`), except the reserved-reminder command which notifies holders of `notification.reserved-reminder`.

## Pipeline invariants (do not break)
- Stages are data (`stages` table, `StageSeeder`), vacancy snapshots freeze config at publish (`WorkflowTemplateSnapshot::createFromTemplate`) — template edits must never mutate in-flight vacancies.
- `ApplicationPipelineService`: forward-only (`advance`/`fail`/`reserve`); `reserved` auto-rejects at vacancy deadline (`AutoRejectReservedKandidat` command). Decisions allowed once per stage (`interviewResult()->exists()` guard).
- Candidates are account-free: status/tests via `token` throttles (`token-access`, `public-submit`, `public-browse`), offers via `signed` + `signed-access` throttle. Do not put these routes behind `auth`.
- Pipeline search uses `ilike` (PostgreSQL); tests run on SQLite — keep queries portable or cover with a feature test.

## Conventions
- UI copy in Bahasa Indonesia; code/comments in English. Follow sibling file structure; Pint style (curly braces always, constructor promotion, explicit return types). PHPUnit only (convert any Pest-style test). Do not create docs/verification scripts unless asked.
