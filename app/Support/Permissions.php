<?php

namespace App\Support;

use App\Models\Role;

/**
 * Manual RBAC permission keys. One button/sidemenu = one permission.
 *
 * Single source of truth: PermissionSeeder builds the DB catalog from
 * self::catalog(), and the access-control UI renders from the DB.
 * Reference these constants in controllers/requests instead of magic strings.
 */
final class Permissions
{
    // Sidemenu
    public const MENU_DASHBOARD = 'menu.dashboard';

    public const MENU_EMPLOYEES = 'menu.employees';

    public const MENU_UNITS = 'menu.units';

    public const MENU_ACCOUNTS = 'menu.accounts';

    public const MENU_WORKFLOW_TEMPLATES = 'menu.workflow-templates';

    public const MENU_JOB_TEMPLATES = 'menu.job-templates';

    public const MENU_VACANCIES = 'menu.vacancies';

    public const MENU_QUESTION_BANK = 'menu.question-bank';

    public const MENU_EMAIL_TEMPLATES = 'menu.email-templates';

    public const MENU_INTERVIEW_TEMPLATES = 'menu.interview-templates';

    public const MENU_RBAC = 'menu.rbac';

    public const MENU_ROLES = 'menu.roles';

    // Dashboard scope
    public const DASHBOARD_VIEW_ORG = 'dashboard.view-org';

    public const DASHBOARD_VIEW_UNIT = 'dashboard.view-unit';

    // Accounts
    public const ACCOUNT_VIEW = 'account.view';

    public const ACCOUNT_CREATE = 'account.create';

    public const ACCOUNT_UPDATE = 'account.update';

    // Employees
    public const EMPLOYEE_VIEW = 'employee.view';

    public const EMPLOYEE_VIEW_SELF = 'employee.view-self';

    public const EMPLOYEE_CREATE = 'employee.create';

    public const EMPLOYEE_UPDATE = 'employee.update';

    public const EMPLOYEE_DELETE = 'employee.delete';

    // Units
    public const UNIT_VIEW = 'unit.view';

    public const UNIT_CREATE = 'unit.create';

    public const UNIT_UPDATE = 'unit.update';

    public const UNIT_DELETE = 'unit.delete';

    // Workflow templates
    public const WORKFLOW_VIEW = 'workflow-template.view';

    public const WORKFLOW_CREATE = 'workflow-template.create';

    public const WORKFLOW_UPDATE = 'workflow-template.update';

    public const WORKFLOW_DELETE = 'workflow-template.delete';

    // Job templates
    public const JOB_TEMPLATE_VIEW = 'job-template.view';

    public const JOB_TEMPLATE_CREATE = 'job-template.create';

    public const JOB_TEMPLATE_UPDATE = 'job-template.update';

    public const JOB_TEMPLATE_DELETE = 'job-template.delete';

    public const JOB_TEMPLATE_PUBLISH = 'job-template.publish';

    public const JOB_TEMPLATE_TEST = 'job-template.test';

    public const JOB_TEMPLATE_INTERVIEW_TEMPLATES = 'job-template.interview-templates';

    // Question bank templates
    public const QUESTION_BANK_VIEW = 'question-bank.view';

    public const QUESTION_BANK_CREATE = 'question-bank.create';

    public const QUESTION_BANK_UPDATE = 'question-bank.update';

    public const QUESTION_BANK_DELETE = 'question-bank.delete';

    // Interview templates
    public const INTERVIEW_TEMPLATE_VIEW = 'interview-template.view';

    public const INTERVIEW_TEMPLATE_CREATE = 'interview-template.create';

    public const INTERVIEW_TEMPLATE_UPDATE = 'interview-template.update';

    public const INTERVIEW_TEMPLATE_DELETE = 'interview-template.delete';

    // Email templates
    public const EMAIL_TEMPLATE_VIEW = 'email-template.view';

    public const EMAIL_TEMPLATE_UPDATE = 'email-template.update';

    // Vacancies
    public const VACANCY_VIEW = 'vacancy.view';

    public const VACANCY_VIEW_ORG = 'vacancy.view-org';

    public const VACANCY_UPDATE = 'vacancy.update';

    public const VACANCY_DELETE = 'vacancy.delete';

    public const VACANCY_CANDIDATE_DETAIL = 'vacancy.candidate-detail';

    public const VACANCY_INTERVIEW_TEMPLATES = 'vacancy.interview-templates';

    public const VACANCY_EXPORT = 'vacancy.export';

    public const VACANCY_CALLBACK = 'vacancy.callback';

    // Pipeline
    public const APPLICATION_ADVANCE = 'application.advance';

    public const APPLICATION_FAIL = 'application.fail';

    // CV screening
    public const SCREENING_DECIDE = 'screening.decide';

    // Interviews
    public const INTERVIEW_SCHEDULE = 'interview.schedule';

    public const INTERVIEW_RESCHEDULE = 'interview.reschedule';

    public const INTERVIEW_DECIDE_USER = 'interview.decide-user';

    public const INTERVIEW_DECIDE_MANAGER = 'interview.decide-manager';

    public const INTERVIEW_DECIDE_DIRECTOR = 'interview.decide-director';

    // Offering / MCU / onboarding
    public const OFFERING_MANAGE = 'offering.manage';

    public const MCU_SCHEDULE = 'mcu.schedule';

    public const MCU_DECIDE = 'mcu.decide';

    public const ONBOARDING_INVITE = 'onboarding.invite';

    public const ONBOARDING_COMPLETE = 'onboarding.complete';

    // Competency tests
    public const TEST_MANAGE = 'test.manage';

    public const TEST_REVIEW_ESSAY = 'test.review-essay';

    public const TEST_DECIDE = 'test.decide';

    // Access control itself
    public const RBAC_MANAGE = 'rbac.manage';

    // Roles
    public const ROLE_VIEW = 'role.view';

    public const ROLE_CREATE = 'role.create';

    public const ROLE_UPDATE = 'role.update';

    public const ROLE_DELETE = 'role.delete';

    // Notifications
    public const NOTIFICATION_RESERVED_REMINDER = 'notification.reserved-reminder';

    /**
     * Full catalog: key => [label, group, default roles].
     * Defaults preserve the pre-RBAC behavior (see TASK-RBAC-MANUAL.md).
     *
     * @return array<string, array{label: string, group: string, roles: list<string>}>
     */
    public static function catalog(): array
    {
        $H = Role::HrAdmin;
        $M = Role::HrManager;
        $U = Role::UnitHead;
        $D = Role::Director;
        $E = Role::Employee;

        return [
            self::MENU_DASHBOARD => ['label' => 'Sidemenu: Beranda', 'group' => 'Menu', 'roles' => [$H, $M, $U, $D, $E]],
            self::MENU_EMPLOYEES => ['label' => 'Sidemenu: Karyawan', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_UNITS => ['label' => 'Sidemenu: Unit', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_ACCOUNTS => ['label' => 'Sidemenu: Akun Pengguna', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_WORKFLOW_TEMPLATES => ['label' => 'Sidemenu: Template Alur Kerja', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_JOB_TEMPLATES => ['label' => 'Sidemenu: Template Lowongan', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_VACANCIES => ['label' => 'Sidemenu: Lowongan Kerja', 'group' => 'Menu', 'roles' => [$H, $M, $U, $D, $E]],
            self::MENU_QUESTION_BANK => ['label' => 'Sidemenu: Template Bank Soal', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_EMAIL_TEMPLATES => ['label' => 'Sidemenu: Template Email', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_INTERVIEW_TEMPLATES => ['label' => 'Sidemenu: Template Wawancara', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_RBAC => ['label' => 'Sidemenu: Hak Akses', 'group' => 'Menu', 'roles' => [$H]],
            self::MENU_ROLES => ['label' => 'Sidemenu: Peran', 'group' => 'Menu', 'roles' => [$H]],

            self::DASHBOARD_VIEW_ORG => ['label' => 'Dasbor seluruh organisasi', 'group' => 'Dasbor', 'roles' => [$H, $M, $D]],
            self::DASHBOARD_VIEW_UNIT => ['label' => 'Dasbor unit sendiri', 'group' => 'Dasbor', 'roles' => [$H, $U, $E]],

            self::ACCOUNT_VIEW => ['label' => 'Lihat daftar akun', 'group' => 'Akun Pengguna', 'roles' => [$H]],
            self::ACCOUNT_CREATE => ['label' => 'Buat akun', 'group' => 'Akun Pengguna', 'roles' => [$H]],
            self::ACCOUNT_UPDATE => ['label' => 'Ubah / aktif-nonaktif akun', 'group' => 'Akun Pengguna', 'roles' => [$H]],

            self::EMPLOYEE_VIEW => ['label' => 'Lihat data semua karyawan', 'group' => 'Karyawan', 'roles' => [$H]],
            self::EMPLOYEE_VIEW_SELF => ['label' => 'Lihat profil sendiri', 'group' => 'Karyawan', 'roles' => [$H, $E]],
            self::EMPLOYEE_CREATE => ['label' => 'Tambah karyawan', 'group' => 'Karyawan', 'roles' => [$H]],
            self::EMPLOYEE_UPDATE => ['label' => 'Ubah karyawan', 'group' => 'Karyawan', 'roles' => [$H]],
            self::EMPLOYEE_DELETE => ['label' => 'Hapus karyawan', 'group' => 'Karyawan', 'roles' => [$H]],

            self::UNIT_VIEW => ['label' => 'Lihat daftar unit', 'group' => 'Unit', 'roles' => [$H]],
            self::UNIT_CREATE => ['label' => 'Tambah unit', 'group' => 'Unit', 'roles' => [$H]],
            self::UNIT_UPDATE => ['label' => 'Ubah unit', 'group' => 'Unit', 'roles' => [$H]],
            self::UNIT_DELETE => ['label' => 'Hapus unit', 'group' => 'Unit', 'roles' => [$H]],

            self::WORKFLOW_VIEW => ['label' => 'Lihat template alur', 'group' => 'Template Alur', 'roles' => [$H]],
            self::WORKFLOW_CREATE => ['label' => 'Buat template alur', 'group' => 'Template Alur', 'roles' => [$H]],
            self::WORKFLOW_UPDATE => ['label' => 'Ubah template alur', 'group' => 'Template Alur', 'roles' => [$H]],
            self::WORKFLOW_DELETE => ['label' => 'Hapus template alur', 'group' => 'Template Alur', 'roles' => [$H]],

            self::JOB_TEMPLATE_VIEW => ['label' => 'Lihat template lowongan', 'group' => 'Template Lowongan', 'roles' => [$H]],
            self::JOB_TEMPLATE_CREATE => ['label' => 'Buat template lowongan', 'group' => 'Template Lowongan', 'roles' => [$H]],
            self::JOB_TEMPLATE_UPDATE => ['label' => 'Ubah template lowongan', 'group' => 'Template Lowongan', 'roles' => [$H]],
            self::JOB_TEMPLATE_DELETE => ['label' => 'Hapus template lowongan', 'group' => 'Template Lowongan', 'roles' => [$H]],
            self::JOB_TEMPLATE_PUBLISH => ['label' => 'Terbitkan lowongan dari template', 'group' => 'Template Lowongan', 'roles' => [$H]],
            self::JOB_TEMPLATE_TEST => ['label' => 'Kelola tes template lowongan', 'group' => 'Template Lowongan', 'roles' => [$H]],
            self::JOB_TEMPLATE_INTERVIEW_TEMPLATES => ['label' => 'Kelola template wawancara job template', 'group' => 'Template Lowongan', 'roles' => [$H]],

            self::QUESTION_BANK_VIEW => ['label' => 'Lihat template bank soal', 'group' => 'Bank Soal', 'roles' => [$H]],
            self::QUESTION_BANK_CREATE => ['label' => 'Buat template bank soal', 'group' => 'Bank Soal', 'roles' => [$H]],
            self::QUESTION_BANK_UPDATE => ['label' => 'Ubah template bank soal', 'group' => 'Bank Soal', 'roles' => [$H]],
            self::QUESTION_BANK_DELETE => ['label' => 'Hapus template bank soal', 'group' => 'Bank Soal', 'roles' => [$H]],

            self::INTERVIEW_TEMPLATE_VIEW => ['label' => 'Lihat template wawancara', 'group' => 'Template Wawancara', 'roles' => [$H]],
            self::INTERVIEW_TEMPLATE_CREATE => ['label' => 'Buat template wawancara', 'group' => 'Template Wawancara', 'roles' => [$H]],
            self::INTERVIEW_TEMPLATE_UPDATE => ['label' => 'Ubah template wawancara', 'group' => 'Template Wawancara', 'roles' => [$H]],
            self::INTERVIEW_TEMPLATE_DELETE => ['label' => 'Hapus template wawancara', 'group' => 'Template Wawancara', 'roles' => [$H]],

            self::EMAIL_TEMPLATE_VIEW => ['label' => 'Lihat template email', 'group' => 'Template Email', 'roles' => [$H]],
            self::EMAIL_TEMPLATE_UPDATE => ['label' => 'Ubah template email', 'group' => 'Template Email', 'roles' => [$H]],

            self::VACANCY_VIEW => ['label' => 'Lihat daftar lowongan', 'group' => 'Lowongan', 'roles' => [$H, $M, $U, $D, $E]],
            self::VACANCY_VIEW_ORG => ['label' => 'Lihat lowongan semua unit', 'group' => 'Lowongan', 'roles' => [$H, $M, $D]],
            self::VACANCY_UPDATE => ['label' => 'Ubah lowongan', 'group' => 'Lowongan', 'roles' => [$H]],
            self::VACANCY_DELETE => ['label' => 'Hapus lowongan', 'group' => 'Lowongan', 'roles' => [$H]],
            self::VACANCY_CANDIDATE_DETAIL => ['label' => 'Buka pipeline & detail kandidat', 'group' => 'Lowongan', 'roles' => [$H, $M, $U, $D, $E]],
            self::VACANCY_INTERVIEW_TEMPLATES => ['label' => 'Kelola template wawancara lowongan', 'group' => 'Lowongan', 'roles' => [$H]],
            self::VACANCY_EXPORT => ['label' => 'Export daftar & PDF kandidat', 'group' => 'Lowongan', 'roles' => [$H]],
            self::VACANCY_CALLBACK => ['label' => 'Panggil kembali kandidat gagal', 'group' => 'Lowongan', 'roles' => [$H]],

            self::APPLICATION_ADVANCE => ['label' => 'Loloskan kandidat ke tahap berikut', 'group' => 'Pipeline', 'roles' => [$H, $M, $U, $D]],
            self::APPLICATION_FAIL => ['label' => 'Gagalkan kandidat', 'group' => 'Pipeline', 'roles' => [$H, $M, $U, $D]],

            self::SCREENING_DECIDE => ['label' => 'Putuskan skrining CV', 'group' => 'Pipeline', 'roles' => [$H, $U, $E]],

            self::INTERVIEW_SCHEDULE => ['label' => 'Jadwalkan wawancara', 'group' => 'Wawancara', 'roles' => [$H, $M]],
            self::INTERVIEW_RESCHEDULE => ['label' => 'Ubah jadwal / pewawancara', 'group' => 'Wawancara', 'roles' => [$H, $M]],
            self::INTERVIEW_DECIDE_USER => ['label' => 'Nilai wawancara user', 'group' => 'Wawancara', 'roles' => [$U, $E]],
            self::INTERVIEW_DECIDE_MANAGER => ['label' => 'Nilai wawancara manajer HR', 'group' => 'Wawancara', 'roles' => [$M]],
            self::INTERVIEW_DECIDE_DIRECTOR => ['label' => 'Nilai wawancara direktur', 'group' => 'Wawancara', 'roles' => [$D]],

            self::OFFERING_MANAGE => ['label' => 'Kirim surat penawaran', 'group' => 'Offering & MCU & Onboarding', 'roles' => [$H]],
            self::MCU_SCHEDULE => ['label' => 'Jadwalkan MCU', 'group' => 'Offering & MCU & Onboarding', 'roles' => [$H]],
            self::MCU_DECIDE => ['label' => 'Putuskan hasil MCU', 'group' => 'Offering & MCU & Onboarding', 'roles' => [$H]],
            self::ONBOARDING_INVITE => ['label' => 'Kirim undangan onboarding', 'group' => 'Offering & MCU & Onboarding', 'roles' => [$H]],
            self::ONBOARDING_COMPLETE => ['label' => 'Selesaikan onboarding', 'group' => 'Offering & MCU & Onboarding', 'roles' => [$H]],

            self::TEST_MANAGE => ['label' => 'Susun tes kompetensi lowongan', 'group' => 'Tes', 'roles' => [$H]],
            self::TEST_REVIEW_ESSAY => ['label' => 'Nilai jawaban esai', 'group' => 'Tes', 'roles' => [$H]],
            self::TEST_DECIDE => ['label' => 'Putuskan hasil tes kompetensi', 'group' => 'Tes', 'roles' => [$H]],

            self::RBAC_MANAGE => ['label' => 'Kelola hak akses (matriks permission)', 'group' => 'Hak Akses', 'roles' => [$H]],

            self::ROLE_VIEW => ['label' => 'Lihat daftar peran', 'group' => 'Peran', 'roles' => [$H]],
            self::ROLE_CREATE => ['label' => 'Buat peran', 'group' => 'Peran', 'roles' => [$H]],
            self::ROLE_UPDATE => ['label' => 'Ubah peran', 'group' => 'Peran', 'roles' => [$H]],
            self::ROLE_DELETE => ['label' => 'Hapus peran', 'group' => 'Peran', 'roles' => [$H]],

            self::NOTIFICATION_RESERVED_REMINDER => ['label' => 'Terima pengingat kandidat ditangguhkan', 'group' => 'Notifikasi', 'roles' => [$H]],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::catalog());
    }
}
