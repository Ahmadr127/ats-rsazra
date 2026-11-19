<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\DiscSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiscSubmission>
 */
class DiscSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'token' => Str::uuid()->toString(),
            'link_tes' => null,
            'catatan' => null,
            'sent_at' => null,
            'proof_paths' => null,
            'proof_uploaded_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state([
            'link_tes' => 'https://disc.example.test/asesmen/'.Str::uuid(),
            'catatan' => 'Kerjakan tes dalam kondisi tenang.',
            'sent_at' => now()->subMinutes(10),
        ]);
    }

    public function withProof(): static
    {
        return $this->sent()->state([
            'proof_paths' => ['test-proofs/demo/disc-1.jpg', 'test-proofs/demo/disc-2.jpg'],
            'proof_uploaded_at' => now(),
        ]);
    }
}
