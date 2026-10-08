<?php

namespace Tests\Feature;

use App\Models\DiscAnswer;
use App\Models\DiscQuestion;
use App\Models\DiscQuestionWord;
use App\Models\DiscSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscQuestionCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'urutan' => 29,
            'words' => [
                ['teks' => 'Tegas Baru', 'dimensi' => 'D'],
                ['teks' => 'Antusias Baru', 'dimensi' => 'I'],
                ['teks' => 'Sabar Baru', 'dimensi' => 'S'],
                ['teks' => 'Teliti Baru', 'dimensi' => 'C'],
            ],
        ], $overrides);
    }

    private function createQuestionWithWords(): DiscQuestion
    {
        $question = DiscQuestion::create(['urutan' => 1]);

        foreach (['D' => 'Tegas', 'I' => 'Antusias', 'S' => 'Sabar', 'C' => 'Teliti'] as $dimensi => $teks) {
            $question->words()->create(['teks' => $teks, 'dimensi' => $dimensi]);
        }

        return $question;
    }

    public function test_index_requires_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('soal-disc.index'))->assertForbidden();
        $this->actingAs(User::factory()->hrAdmin()->create())->get(route('soal-disc.index'))->assertOk();
    }

    public function test_store_rejects_incomplete_dimension_set(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('soal-disc.store'), $this->validData([
            'words' => [
                ['teks' => 'Kata D1', 'dimensi' => 'D'],
                ['teks' => 'Kata D2', 'dimensi' => 'D'],
                ['teks' => 'Kata S', 'dimensi' => 'S'],
                ['teks' => 'Kata C', 'dimensi' => 'C'],
            ],
        ]));

        $response->assertSessionHasErrors('words');
        $this->assertDatabaseCount('disc_questions', 0);
    }

    public function test_store_creates_question_with_words(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('soal-disc.store'), $this->validData());

        $response->assertRedirect(route('soal-disc.index'));

        $question = DiscQuestion::where('urutan', 29)->firstOrFail();
        $this->assertCount(4, $question->words);
        $this->assertEqualsCanonicalizing(
            ['C', 'D', 'I', 'S'],
            $question->words->map(fn (DiscQuestionWord $word) => $word->dimensi->value)->all()
        );
    }

    public function test_update_changes_words(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $question = $this->createQuestionWithWords();
        $wordIds = $question->words()->orderBy('id')->pluck('id')->all();

        $response = $this->actingAs($admin)->put(route('soal-disc.update', $question), [
            'urutan' => 2,
            'words' => [
                ['id' => $wordIds[0], 'teks' => 'Tegas Ubahan', 'dimensi' => 'D'],
                ['id' => $wordIds[1], 'teks' => 'Antusias', 'dimensi' => 'I'],
                ['id' => $wordIds[2], 'teks' => 'Sabar', 'dimensi' => 'S'],
                ['id' => $wordIds[3], 'teks' => 'Teliti', 'dimensi' => 'C'],
            ],
        ]);

        $response->assertRedirect(route('soal-disc.index'));
        $this->assertDatabaseHas('disc_questions', ['id' => $question->id, 'urutan' => 2]);
        $this->assertDatabaseHas('disc_question_words', ['id' => $wordIds[0], 'teks' => 'Tegas Ubahan']);
    }

    public function test_destroy_is_blocked_when_answers_exist(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $question = $this->createQuestionWithWords();
        $words = $question->words()->orderBy('id')->get();
        $submission = DiscSubmission::factory()->create();

        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $question->id,
            'most_disc_word_id' => $words[0]->id,
            'least_disc_word_id' => $words[1]->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('soal-disc.destroy', $question));

        $response->assertSessionHasErrors('soal');
        $this->assertDatabaseHas('disc_questions', ['id' => $question->id]);
    }

    public function test_destroy_deletes_unused_question_and_words(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $question = $this->createQuestionWithWords();

        $response = $this->actingAs($admin)->delete(route('soal-disc.destroy', $question));

        $response->assertRedirect(route('soal-disc.index'));
        $this->assertDatabaseMissing('disc_questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('disc_question_words', ['disc_question_id' => $question->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('soal-disc.index'))->assertRedirect(route('login'));
    }
}
