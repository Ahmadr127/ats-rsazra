<?php

namespace Database\Factories;

use App\Models\Role as RoleRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => RoleRecord::firstOrCreate(
                ['key' => RoleRecord::Employee],
                ['label' => RoleRecord::defaultLabel(RoleRecord::Employee)]
            )->id,
            'must_change_password' => false,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function withRole(string $roleKey): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => RoleRecord::firstOrCreate(
                ['key' => $roleKey],
                ['label' => RoleRecord::defaultLabel($roleKey)]
            )->id,
        ]);
    }

    public function mustChangePassword(bool $value = true): static
    {
        return $this->state(fn (array $attributes) => [
            'must_change_password' => $value,
        ]);
    }

    public function hrAdmin(): static
    {
        return $this->withRole(RoleRecord::HrAdmin);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
