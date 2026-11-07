<?php

namespace Tests\Feature;

use App\Models\Survey;
use App\Models\TextRevision;
use App\Models\User;
use App\Services\ReviewableText;
use App\Services\TextReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;

class TextReviewTest extends \Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['text_review.enabled' => true, 'text_review.api_key' => 'test-key', 'text_review.base_url' => 'https://api.anthropic.com']);
        Http::preventStrayRequests();
    }

    public function test_corrects_only_the_requested_text_and_preserves_an_already_clear_sentence(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));

        $results = app(TextReviewService::class)->reviewFields([
            'purpose' => 'Reunion de démarrage.',
            'observations' => 'Commande des équipements actifs.',
        ]);

        $this->assertSame('Réunion de démarrage.', $results['purpose']['value']);
        $this->assertFalse($results['observations']['changed']);
        Http::assertSentCount(1);
    }

    public function test_redacts_sensitive_values_including_a_secret_split_across_formatting_runs(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $delta = ['ops' => [
            ['insert' => 'Reunion. Mot de passe : '],
            ['insert' => 'super-secret-example', 'attributes' => ['bold' => true]],
            ['insert' => "\nContact : agent@example.com, IP 192.168.1.12, SN ABC12345.\n"],
        ]];

        $result = app(TextReviewService::class)->reviewFields(['layout_content' => json_encode($delta)]);
        $corrected = json_decode($result['layout_content']['value'], true);

        $this->assertSame($delta['ops'][1], $corrected['ops'][1]);
        $this->assertSame($delta['ops'][2], $corrected['ops'][2]);
        Http::assertSent(function (ClientRequest $request): bool {
            $payload = json_encode($request->data());

            return ! str_contains($payload, 'super-secret-example')
                && ! str_contains($payload, 'agent@example.com')
                && ! str_contains($payload, '192.168.1.12')
                && ! str_contains($payload, 'ABC12345');
        });
    }

    public function test_turns_an_unapplied_accent_remark_into_a_savable_proposal(): void
    {
        $attempts = 0;
        Http::fake(['api.anthropic.com/*' => function (ClientRequest $request) use (&$attempts) {
            $attempts++;
            $segments = json_decode($request['messages'][0]['content'], true);
            foreach ($segments as &$segment) {
                $segment['text'] = $attempts === 1 ? $segment['text'] : str_replace('complete', 'complète', $segment['text']);
                $segment['note'] = $attempts === 1 ? 'Accent manquant possible : « complète » ?' : '';
            }

            return Http::response(['stop_reason' => 'tool_use', 'content' => [[
                'type' => 'tool_use', 'name' => 'review_text', 'input' => ['segments' => $segments],
            ]]]);
        }]);
        $survey = Survey::factory()->create(['purpose' => 'Couverture Wifi complete', 'status' => 'validated']);

        $preview = $this->actingAs($survey->user)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose'])
            ->assertSuccessful()->assertJsonPath('fields.purpose.changed', true)
            ->assertJsonPath('fields.purpose.value', 'Couverture Wifi complète');

        $this->assertNotEmpty($preview->json('fields.purpose.token'));
        $this->putJson(route('text-reviews.apply', ['surveys', $survey->id]), ['token' => $preview->json('fields.purpose.token')])->assertSuccessful();
        $this->assertSame('Couverture Wifi complète', $survey->fresh()->purpose);
        Http::assertSentCount(2);
    }

    public function test_keeps_factual_questions_on_their_own_field_without_retrying(): void
    {
        Http::fake(['api.anthropic.com/*' => function (ClientRequest $request) {
            $segments = json_decode($request['messages'][0]['content'], true);
            foreach ($segments as &$segment) {
                $segment['note'] = str_contains($segment['text'], 'stockage') ? 'Le lieu de stockage est-il disponible ou manquant ?' : '';
            }

            return Http::response(['stop_reason' => 'tool_use', 'content' => [[
                'type' => 'tool_use', 'name' => 'review_text', 'input' => ['segments' => $segments],
            ]]]);
        }]);

        $results = app(TextReviewService::class)->reviewFields([
            'purpose' => 'Couverture Wi-Fi complète.', 'observations' => 'Lieu de stockage adéquat.',
        ]);
        $this->assertSame('', $results['purpose']['note']);
        $this->assertSame('Le lieu de stockage est-il disponible ou manquant ?', $results['observations']['note']);
        $this->assertFalse($results['observations']['changed']);
        Http::assertSentCount(1);
    }

    public function test_does_not_loop_when_the_model_keeps_returning_an_unapplied_spelling_remark(): void
    {
        Http::fake(['api.anthropic.com/*' => function (ClientRequest $request) {
            $segments = json_decode($request['messages'][0]['content'], true);
            foreach ($segments as &$segment) {
                $segment['note'] = 'Accent manquant possible.';
            }

            return Http::response(['stop_reason' => 'tool_use', 'content' => [[
                'type' => 'tool_use', 'name' => 'review_text', 'input' => ['segments' => $segments],
            ]]]);
        }]);

        $result = app(TextReviewService::class)->reviewFields(['purpose' => 'Couverture Wifi complete']);
        $this->assertFalse($result['purpose']['changed']);
        $this->assertSame('Couverture Wifi complete', $result['purpose']['value']);
        Http::assertSentCount(2);
    }

    public function test_keeps_rich_text_attributes_and_embeds_intact(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $delta = ['ops' => [
            ['insert' => 'Reunion', 'attributes' => ['bold' => true, 'color' => '#123456']],
            ['insert' => "\n", 'attributes' => ['list' => 'bullet']],
            ['insert' => ['image' => 'https://example.com/private-image.png']],
            ['insert' => "\n"],
        ]];

        $result = app(TextReviewService::class)->reviewFields(['layout_content' => json_encode($delta)]);
        $delta['ops'][0]['insert'] = 'Réunion';

        $this->assertSame($delta, json_decode($result['layout_content']['value'], true));
        Http::assertSent(fn (ClientRequest $request): bool => ! str_contains(json_encode($request->data()), 'private-image'));
    }

    public function test_rejects_suggestions_that_change_quantities_or_remove_a_protected_reference(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('4 points', '5 points', $text));
        $text = 'Installation de 4 points d’accès.';

        $result = app(TextReviewService::class)->reviewFields(['purpose' => $text]);

        $this->assertSame($text, $result['purpose']['value']);
        $this->assertFalse($result['purpose']['changed']);
    }

    public function test_denies_access_to_another_users_document_before_calling_anthropic(): void
    {
        $survey = Survey::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose'])
            ->assertForbidden();
        $this->getJson(route('text-reviews.history', ['surveys', $survey->id]))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_rejects_attempts_to_correct_identity_or_structured_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('text-reviews.preview'), [
            'type' => 'surveys', 'fields' => ['contact_email' => 'person@example.com'],
        ])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_applies_an_approved_correction_to_a_validated_document_and_archives_its_original(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $survey = Survey::factory()->create(['purpose' => 'Reunion de démarrage.', 'status' => 'validated']);
        $signature = $survey->signature()->create(['signer_name' => 'Signataire', 'signature_data' => 'original-signature', 'signed_at' => now()]);

        $preview = $this->actingAs($survey->user)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose'])->assertSuccessful();
        $this->assertSame('Reunion de démarrage.', $survey->fresh()->purpose);

        $this->putJson(route('text-reviews.apply', ['surveys', $survey->id]), ['token' => $preview->json('fields.purpose.token')])->assertSuccessful();

        $revision = TextRevision::query()->firstOrFail();
        $this->assertSame('Réunion de démarrage.', $survey->fresh()->purpose);
        $this->assertSame('validated', $survey->fresh()->status);
        $this->assertSame('original-signature', $signature->fresh()->signature_data);
        $this->assertTrue($revision->after_validation);
        $this->assertSame('Reunion de démarrage.', $revision->original_snapshot['purpose']);
        $this->assertSame('original-signature', $revision->original_snapshot['signature']['signature_data']);
        $this->assertStringNotContainsString('Reunion', $revision->getRawOriginal('original_text'));

        $this->get(route('surveys.show', $survey))->assertSuccessful()->assertSee('corrections éditoriales après validation');
    }

    public function test_refuses_stale_or_tampered_proposals_without_overwriting_a_newer_edit(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $survey = Survey::factory()->create(['purpose' => 'Reunion de démarrage.']);
        $preview = $this->actingAs($survey->user)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose']);
        $survey->update(['purpose' => 'Un nouveau texte.']);

        $this->putJson(route('text-reviews.apply', ['surveys', $survey->id]), ['token' => $preview->json('fields.purpose.token')])->assertConflict();
        $this->putJson(route('text-reviews.apply', ['surveys', $survey->id]), ['token' => 'invalid'])->assertUnprocessable();

        $this->assertSame('Un nouveau texte.', $survey->fresh()->purpose);
        $this->assertSame(0, TextRevision::query()->count());
    }

    public function test_identifies_an_empty_quill_document_rather_than_displaying_an_empty_viewer(): void
    {
        $survey = Survey::factory()->create(['layout_content' => '{"ops":[{"insert":"\n"}]}', 'status' => 'validated']);

        $this->assertFalse(ReviewableText::hasContent($survey->layout_content));
        $this->assertFalse(ReviewableText::hasContent('<p><br></p>'));
        $this->assertTrue(ReviewableText::hasContent('{"ops":[{"insert":{"image":"example.png"}}]}'));

        $this->actingAs($survey->user)->get(route('surveys.show', $survey))
            ->assertSuccessful()->assertSee("La mise en page n'a pas encore été complétée.", false)
            ->assertDontSee('Chargement du contenu...')->assertDontSee('Compléter la mise en page');
    }

    public function test_returns_a_recoverable_error_when_anthropic_is_unavailable_and_never_changes_data(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['error' => ['message' => 'unavailable']], 503)]);
        $survey = Survey::factory()->create(['purpose' => 'Reunion de démarrage.']);

        $this->actingAs($survey->user)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose'])->assertStatus(503);

        $this->assertSame('Reunion de démarrage.', $survey->fresh()->purpose);
        $this->assertSame(0, TextRevision::query()->count());
    }

    public function test_keeps_an_audit_when_a_form_saves_an_accepted_correction(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $survey = Survey::factory()->create(['purpose' => 'Reunion de démarrage.', 'layout_content' => null]);
        $preview = $this->actingAs($survey->user)->postJson(route('text-reviews.preview'), [
            'type' => 'surveys', 'id' => $survey->id, 'fields' => ['purpose' => $survey->purpose],
        ])->assertSuccessful();

        $this->put(route('surveys.update', $survey), [
            ...$survey->only(['subsidiary', 'opportunity_name', 'company_name', 'location', 'contact_name', 'contact_function', 'contact_phone', 'contact_email']),
            'start_datetime' => $survey->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $survey->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $preview->json('fields.purpose.value'),
            '_ai_reviews' => ['purpose' => $preview->json('fields.purpose.token')],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $revision = TextRevision::query()->firstOrFail();
        $this->assertSame('Reunion de démarrage.', $revision->original_text);
        $this->assertSame('Réunion de démarrage.', $revision->corrected_text);
        $this->assertFalse($revision->after_validation);
    }

    public function test_archives_the_source_text_when_creating_a_record_from_an_ai_review(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $user = User::factory()->create();
        $preview = $this->actingAs($user)->postJson(route('text-reviews.preview'), [
            'type' => 'surveys', 'fields' => ['purpose' => 'Reunion de démarrage.'],
        ])->assertSuccessful();

        $this->post(route('surveys.store'), [
            ...Survey::factory()->make()->only(['subsidiary', 'opportunity_name', 'company_name', 'location', 'contact_name', 'contact_function', 'contact_phone', 'contact_email']),
            'start_datetime' => '2026-01-10 08:00:00', 'end_datetime' => '2026-01-10 10:00:00',
            'purpose' => $preview->json('fields.purpose.value'),
            '_ai_reviews' => ['purpose' => $preview->json('fields.purpose.token')],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $revision = TextRevision::query()->firstOrFail();
        $this->assertSame('Reunion de démarrage.', $revision->original_text);
        $this->assertSame('Reunion de démarrage.', $revision->original_snapshot['purpose']);
    }

    public function test_rejects_a_proposal_that_is_expired_or_belongs_to_another_user(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('Reunion', 'Réunion', $text));
        $survey = Survey::factory()->create(['purpose' => 'Reunion de démarrage.']);
        $preview = $this->actingAs($survey->user)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose']);
        $token = $preview->json('fields.purpose.token');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson(route('text-reviews.apply', ['surveys', $survey->id]), ['token' => $token])->assertUnprocessable();

        $this->actingAs($survey->user);
        $this->travel(3)->hours();
        $this->withSession(['auth.last_activity_at' => now()->timestamp])
            ->putJson(route('text-reviews.apply', ['surveys', $survey->id]), ['token' => $token])->assertUnprocessable();
        $this->travelBack();
        $this->assertSame('Reunion de démarrage.', $survey->fresh()->purpose);
    }

    public function test_discards_truncated_or_malformed_model_output(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['stop_reason' => 'max_tokens', 'content' => []])]);
        $survey = Survey::factory()->create(['purpose' => 'Reunion de démarrage.']);

        $this->actingAs($survey->user)->postJson(route('text-reviews.saved-preview', ['surveys', $survey->id]), ['field' => 'purpose'])->assertStatus(503);
        $this->assertSame(0, TextRevision::query()->count());
    }

    public function test_preserves_negations_and_leaves_empty_content_untouched(): void
    {
        fakeEditorialReview(fn (string $text): string => str_replace('pas ', '', $text));
        $result = app(TextReviewService::class)->reviewFields(['purpose' => 'Le switch ne fonctionne pas actuellement.']);
        $this->assertFalse($result['purpose']['changed']);
        $result = app(TextReviewService::class)->reviewFields(['layout_content' => '{"ops":[{"insert":"\n"}]}']);
        $this->assertFalse($result['layout_content']['changed']);
        Http::assertSentCount(1);
    }
}

function fakeEditorialReview(\Closure $correct): void
{
    Http::fake(['api.anthropic.com/*' => function (ClientRequest $request) use ($correct) {
        $segments = json_decode($request['messages'][0]['content'], true);
        foreach ($segments as &$segment) {
            $segment['text'] = $correct($segment['text']);
            $segment['note'] = '';
        }

        return Http::response(['stop_reason' => 'tool_use', 'content' => [[
            'type' => 'tool_use', 'name' => 'review_text', 'input' => ['segments' => $segments],
        ]]]);
    }]);
}
