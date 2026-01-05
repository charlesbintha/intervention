<?php

use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows owner to validate their survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->post(route('surveys.validate', $survey));

    $response->assertRedirect(route('surveys.show', $survey));
    $response->assertSessionHas('success', 'Survey validé avec succès.');

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
        'status' => 'validated',
    ]);
});

it('prevents non-owner from validating survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($other)
        ->post(route('surveys.validate', $survey));

    $response->assertForbidden();

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
        'status' => 'draft',
    ]);
});

it('prevents admin from validating via validate endpoint', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('surveys.validate', $survey));

    $response->assertForbidden();

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
        'status' => 'draft',
    ]);
});

it('prevents editing validated survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('surveys.edit', $survey));

    $response->assertForbidden();
});

it('prevents updating validated survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
        'company_name' => 'Original Name',
    ]);

    $response = $this->actingAs($owner)
        ->put(route('surveys.update', $survey), [
            'opportunity_name' => $survey->opportunity_name,
            'company_name' => 'Updated Name',
            'location' => $survey->location,
            'contact_name' => $survey->contact_name,
            'contact_function' => $survey->contact_function,
            'contact_phone' => $survey->contact_phone,
            'contact_email' => $survey->contact_email,
            'start_datetime' => $survey->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $survey->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $survey->purpose,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
        'company_name' => 'Original Name',
    ]);
});

it('prevents deleting validated survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->delete(route('surveys.destroy', $survey));

    $response->assertForbidden();

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
    ]);
});

it('prevents double validation', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->post(route('surveys.validate', $survey));

    $response->assertRedirect(route('surveys.show', $survey));
    $response->assertSessionHas('info', 'Ce survey est déjà validé.');
});

it('allows owner to edit non-validated survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('surveys.edit', $survey));

    $response->assertSuccessful();
});

it('allows owner to update non-validated survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'company_name' => 'Original Name',
    ]);

    $response = $this->actingAs($owner)
        ->put(route('surveys.update', $survey), [
            'opportunity_name' => $survey->opportunity_name,
            'company_name' => 'Updated Name',
            'location' => $survey->location,
            'contact_name' => $survey->contact_name,
            'contact_function' => $survey->contact_function,
            'contact_phone' => $survey->contact_phone,
            'contact_email' => $survey->contact_email,
            'start_datetime' => $survey->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $survey->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $survey->purpose,
        ]);

    $response->assertRedirect(route('surveys.show', $survey));

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
        'company_name' => 'Updated Name',
    ]);
});

it('allows owner to delete non-validated survey', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $survey = Survey::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->delete(route('surveys.destroy', $survey));

    $response->assertRedirect(route('surveys.index'));

    $this->assertDatabaseMissing('surveys', [
        'id' => $survey->id,
    ]);
});
