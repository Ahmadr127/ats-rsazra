<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            StageSeeder::class,
            WorkflowTemplateSeeder::class,
            UnitSeeder::class,
            SimutuOrganisasiSeeder::class,
            EmailTemplateSeeder::class,
            InterviewTemplateSeeder::class,
            DiscQuestionSeeder::class,
            MbtiQuestionSeeder::class,
            QuestionBankTemplateSeeder::class,
            JobTemplateSeeder::class,
            VacancySeeder::class,
        ]);

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role_id' => Role::where('key', Role::HrAdmin)->firstOrFail()->id,
                'must_change_password' => true,
                'is_active' => true,
            ]
        );
    }
}
