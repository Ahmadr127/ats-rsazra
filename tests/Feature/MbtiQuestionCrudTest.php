<?php

namespace Tests\Feature;

use App\Models\MbtiAnswer;
use App\Models\MbtiQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MbtiQuestionCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'urutan' => 71,
            'dikotomi' => 'EI',
            'kutub_a' => 'E',
            'pernyataan_a' => 'Saya berenergi saat bertemu banyak orang.',
            'pernyataan_b' => 'Saya lelah setelah lama bertemu banyak orang.',
        ], $overrides);
    }

    public function test_index_requires_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('soal-mbti.index'))->assertForbidden();
        $this->actingAs(User::factory()->hrAdmin()->create())->get(route('soal-mbti.index'))->assertOk();
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs(User::factory()->hrAdmin()->create())->get(route('soal-mbti.create'));

        $response->assertOk();
        $response->assertSee('Buat Soal MBTI', false);
    }

    public function test_store_rejects_kutub_outside_dikotomi(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(
            route('soal-mbti.store'),
            $this->validData(['dikotomi' => 'EI', 'kutub_a' => 'T'])
        );

        $response->assertSessionHasErrors('kutub_a');
        $this->assertDatabaseCount('mbti_questions', 0);
    }

    public function test_store_creates_question(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('soal-mbti.store'), $this->validData());

        $response->assertRedirect(route('soal-mbti.index'));
        $this->assertDatabaseHas('mbti_questions', [
            'urutan' => 71,
            'dikotomi' => 'EI',
            'kutub_a' => 'E',
        ]);
    }

    public function test_update_changes_question(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $question = MbtiQuestion::factory()->create(['dikotomi' => 'EI', 'kutub_a' => 'E']);

        $response = $this->actingAs($admin)->put(
            route('soal-mbti.update', $question),
            $this->validData(['urutan' => 72, 'dikotomi' => 'SN', 'kutub_a' => 'N'])
        );

        $response->assertRedirect(route('soal-mbti.index'));
        $this->assertDatabaseHas('mbti_questions', [
            'id' => $question->id,
            'urutan' => 72,
            'dikotomi' => 'SN',
            'kutub_a' => 'N',
        ]);
    }

    public function test_destroy_is_blocked_when_answers_exist(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $question = MbtiQuestion::factory()->create();
        MbtiAnswer::factory()->create(['mbti_question_id' => $question->id]);

        $response = $this->actingAs($admin)->delete(route('soal-mbti.destroy', $question));

        $response->assertSessionHasErrors('soal');
        $this->assertDatabaseHas('mbti_questions', ['id' => $question->id]);
    }

    public function test_destroy_deletes_unused_question(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $question = MbtiQuestion::factory()->create();

        $response = $this->actingAs($admin)->delete(route('soal-mbti.destroy', $question));

        $response->assertRedirect(route('soal-mbti.index'));
        $this->assertDatabaseMissing('mbti_questions', ['id' => $question->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('soal-mbti.index'))->assertRedirect(route('login'));
    }
}
