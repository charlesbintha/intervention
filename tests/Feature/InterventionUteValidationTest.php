<?php

use App\Models\InterventionUte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows owner to validate their intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->post(route('intervention-utes.validate', $intervention));

    $response->assertRedirect(route('intervention-utes.show', $intervention));
    $response->assertSessionHas('success', 'Intervention validÃ©e avec succÃ¨s.');

    $this->assertDatabaseHas('intervention_utes', [
        'id' => $intervention->id,
        'status' => 'validated',
    ]);
});

it('prevents non-owner from validating intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($other)
        ->post(route('intervention-utes.validate', $intervention));

    $response->assertForbidden();

    $this->assertDatabaseHas('intervention_utes', [
        'id' => $intervention->id,
        'status' => 'draft',
    ]);
});

it('prevents admin from validating via validate endpoint', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('intervention-utes.validate', $intervention));

    $response->assertForbidden();

    $this->assertDatabaseHas('intervention_utes', [
        'id' => $intervention->id,
        'status' => 'draft',
    ]);
});

it('prevents editing validated intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('intervention-utes.edit', $intervention));

    $response->assertForbidden();
});

it('prevents updating validated intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
        'company_name' => 'Original Name',
    ]);

    $response = $this->actingAs($owner)
        ->put(route('intervention-utes.update', $intervention), [
            'company_name' => 'Updated Name',
            'location' => $intervention->location,
            'contact_name' => $intervention->contact_name,
            'contact_function' => $intervention->contact_function,
            'contact_phone' => $intervention->contact_phone,
            'contact_email' => $intervention->contact_email,
            'start_datetime' => $intervention->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $intervention->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $intervention->purpose,
            'diagnostic' => $intervention->diagnostic,
            'type' => $intervention->type,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('intervention_utes', [
        'id' => $intervention->id,
        'company_name' => 'Original Name',
    ]);
});

it('prevents deleting validated intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->delete(route('intervention-utes.destroy', $intervention));

    $response->assertForbidden();

    $this->assertDatabaseHas('intervention_utes', [
        'id' => $intervention->id,
    ]);
});

it('prevents double validation', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->post(route('intervention-utes.validate', $intervention));

    $response->assertRedirect(route('intervention-utes.show', $intervention));
    $response->assertSessionHas('info', 'Cette intervention est dÃ©jÃ  validÃ©e.');
});

it('allows owner to edit non-validated intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('intervention-utes.edit', $intervention));

    $response->assertSuccessful();
});

it('allows owner to update non-validated intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'company_name' => 'Original Name',
    ]);

    $response = $this->actingAs($owner)
        ->put(route('intervention-utes.update', $intervention), [
            'company_name' => 'Updated Name',
            'location' => $intervention->location,
            'contact_name' => $intervention->contact_name,
            'contact_function' => $intervention->contact_function,
            'contact_phone' => $intervention->contact_phone,
            'contact_email' => $intervention->contact_email,
            'start_datetime' => $intervention->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $intervention->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $intervention->purpose,
            'diagnostic' => $intervention->diagnostic,
            'type' => $intervention->type,
        ]);

    $response->assertRedirect(route('intervention-utes.show', $intervention));

    $this->assertDatabaseHas('intervention_utes', [
        'id' => $intervention->id,
        'company_name' => 'Updated Name',
    ]);
});

it('allows owner to delete non-validated intervention', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $intervention = InterventionUte::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->delete(route('intervention-utes.destroy', $intervention));

    $response->assertRedirect(route('intervention-utes.index'));

    $this->assertDatabaseMissing('intervention_utes', [
        'id' => $intervention->id,
    ]);
});
