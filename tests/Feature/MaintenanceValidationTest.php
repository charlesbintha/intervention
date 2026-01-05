<?php

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows owner to validate their maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->post(route('maintenances.validate', $maintenance));

    $response->assertRedirect(route('maintenances.show', $maintenance));
    $response->assertSessionHas('success', 'Maintenance validée avec succès.');

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'status' => 'validated',
    ]);
});

it('prevents non-owner from validating maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($other)
        ->post(route('maintenances.validate', $maintenance));

    $response->assertForbidden();

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'status' => 'draft',
    ]);
});

it('prevents admin from validating via validate endpoint', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('maintenances.validate', $maintenance));

    $response->assertForbidden();

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'status' => 'draft',
    ]);
});

it('prevents editing validated maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('maintenances.edit', $maintenance));

    $response->assertForbidden();
});

it('prevents updating validated maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
        'company_name' => 'Original Name',
    ]);

    $response = $this->actingAs($owner)
        ->put(route('maintenances.update', $maintenance), [
            'project_name' => $maintenance->project_name,
            'company_name' => 'Updated Name',
            'location' => $maintenance->location,
            'contact_name' => $maintenance->contact_name,
            'contact_function' => $maintenance->contact_function,
            'contact_phone' => $maintenance->contact_phone,
            'contact_email' => $maintenance->contact_email,
            'start_datetime' => $maintenance->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $maintenance->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $maintenance->purpose,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'company_name' => 'Original Name',
    ]);
});

it('prevents deleting validated maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->delete(route('maintenances.destroy', $maintenance));

    $response->assertForbidden();

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
    ]);
});

it('prevents double validation', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'validated',
    ]);

    $response = $this->actingAs($owner)
        ->post(route('maintenances.validate', $maintenance));

    $response->assertRedirect(route('maintenances.show', $maintenance));
    $response->assertSessionHas('info', 'Cette maintenance est déjà validée.');
});

it('allows owner to edit non-validated maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('maintenances.edit', $maintenance));

    $response->assertSuccessful();
});

it('allows owner to update non-validated maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'company_name' => 'Original Name',
    ]);

    $response = $this->actingAs($owner)
        ->put(route('maintenances.update', $maintenance), [
            'project_name' => $maintenance->project_name,
            'company_name' => 'Updated Name',
            'location' => $maintenance->location,
            'contact_name' => $maintenance->contact_name,
            'contact_function' => $maintenance->contact_function,
            'contact_phone' => $maintenance->contact_phone,
            'contact_email' => $maintenance->contact_email,
            'start_datetime' => $maintenance->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $maintenance->end_datetime->format('Y-m-d H:i:s'),
            'purpose' => $maintenance->purpose,
        ]);

    $response->assertRedirect(route('maintenances.show', $maintenance));

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'company_name' => 'Updated Name',
    ]);
});

it('allows owner to delete non-validated maintenance', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $maintenance = Maintenance::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($owner)
        ->delete(route('maintenances.destroy', $maintenance));

    $response->assertRedirect(route('maintenances.index'));

    $this->assertDatabaseMissing('maintenances', [
        'id' => $maintenance->id,
    ]);
});
