<?php

use App\Models\ProjectAction;
use App\Models\ProjectActivity;
use App\Models\ProjectBlocker;
use App\Models\ProjectTracking;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('creates a project tracking with the executing subsidiary from the project', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->mock(ProjectService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getTrackableProjectByCode')
            ->once()
            ->with('PRJ-001')
            ->andReturn([
                'code_projet' => 'PRJ-001',
                'nom_projet' => 'Déploiement réseau',
                'opportunity_id' => '',
                'client_name' => 'Client Test',
                'subsidiary' => 'UTE',
            ]);
    });

    $response = $this->actingAs($user)->post(route('project-trackings.store'), [
        'external_project_code' => 'PRJ-001',
        'subsidiary' => 'GUT',
        'client_name' => 'Client Test',
        'location' => 'Dakar',
        'current_start_date' => '2026-08-24',
        'current_end_date' => '2026-09-30',
    ]);

    $tracking = ProjectTracking::firstOrFail();
    $response->assertRedirect(route('project-trackings.show', $tracking));
    expect($tracking->user_id)->toBe($user->id)
        ->and($tracking->subsidiary)->toBe('UTE')
        ->and($tracking->status)->toBe('draft');
});

it('only exposes non-deleted projects with trackable statuses', function () {
    Cache::forget('projects_list_v2');
    config([
        'services.projects.api_url' => 'https://projects.test/api/projects',
        'services.projects.api_key' => 'test-key',
    ]);

    Http::fake([
        'https://projects.test/*' => Http::response(['items' => [
            ['code_projet' => 'PRJ-001', 'nom_projet' => 'Projet en cours', 'statut_initial' => 'En cours', 'filiale_executant' => 'Groupe Univers Télécom', 'deleted_at' => null],
            ['code_projet' => 'PRJ-002', 'nom_projet' => 'Projet planifié', 'statut_initial' => 'Planifié', 'filiale_executant' => 'Cabinet Pencco', 'deleted_at' => null],
            ['code_projet' => 'PRJ-003', 'nom_projet' => 'Projet en pause', 'statut_initial' => 'Pause', 'filiale_executant' => 'Univers Technology & Energies', 'deleted_at' => null],
            ['code_projet' => 'PRJ-004', 'nom_projet' => 'Projet terminé', 'statut_initial' => 'Terminé', 'filiale_executant' => 'Groupe Univers Télécom', 'deleted_at' => null],
            ['code_projet' => 'PRJ-005', 'nom_projet' => 'Projet supprimé', 'statut_initial' => 'En cours', 'filiale_executant' => 'Groupe Univers Télécom', 'deleted_at' => '2026-08-31 10:00:00'],
        ]]),
    ]);

    $projects = app(ProjectService::class)->getTrackableProjects();

    expect($projects->pluck('code_projet')->all())->toBe(['PRJ-001', 'PRJ-002', 'PRJ-003'])
        ->and($projects->pluck('subsidiary')->all())->toBe(['GUT', 'CP', 'UTE']);
});

it('prevents a regular user from viewing another users tracking', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('project-trackings.show', $tracking))->assertForbidden();
});

it('allows an admin to view another users tracking', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    $tracking = ProjectTracking::factory()->for($owner)->create();

    $this->actingAs($admin)->get(route('project-trackings.edit', $tracking))->assertSuccessful();
});

it('requires at least one activity before approving the initial plan', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('project-trackings.approve-baseline', $tracking))
        ->assertSessionHas('error');

    expect($tracking->fresh()->baseline_approved_at)->toBeNull();
});

it('locks the initial plan without activity weights', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    $first = ProjectActivity::factory()->for($tracking)->create();
    $second = ProjectActivity::factory()->for($tracking)->create();

    $this->actingAs($user)
        ->post(route('project-trackings.approve-baseline', $tracking))
        ->assertSessionHas('success');

    expect($tracking->fresh()->status)->toBe('active')
        ->and($tracking->fresh()->baseline_approved_at)->not->toBeNull()
        ->and($first->fresh()->baseline_start_date->toDateString())->toBe($first->current_start_date->toDateString())
        ->and($second->fresh()->baseline_end_date->toDateString())->toBe($second->current_end_date->toDateString());

    $this->assertDatabaseHas('plan_revisions', ['project_tracking_id' => $tracking->id, 'version' => 1]);
});

it('updates activity progress from work declarations', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    $activity = ProjectActivity::factory()->for($tracking)->create(['planned_quantity' => 100, 'completed_quantity' => 0]);
    $startedAt = now()->subDays(3)->startOfMinute();
    $endedAt = now()->subDay()->startOfMinute();

    $this->actingAs($user)->post(route('project-trackings.work-logs.store', $tracking), [
        'project_activity_id' => $activity->id,
        'started_at' => $startedAt->format('Y-m-d\TH:i'),
        'ended_at' => $endedAt->format('Y-m-d\TH:i'),
        'quantity_completed' => 25,
        'work_description' => 'Installation des équipements du premier étage.',
    ])->assertSessionHas('success');

    expect((float) $activity->fresh()->completed_quantity)->toBe(25.0)
        ->and($activity->fresh()->progress_percentage)->toBe(25.0)
        ->and($activity->fresh()->status)->toBe('in_progress');

    $this->assertDatabaseHas('work_logs', [
        'project_activity_id' => $activity->id,
        'started_at' => $startedAt->format('Y-m-d H:i:s'),
        'ended_at' => $endedAt->format('Y-m-d H:i:s'),
    ]);
});

it('automatically orders activities and stores selected people', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    ProjectActivity::factory()->for($tracking)->create(['sort_order' => 3]);

    $this->actingAs($user)->post(route('project-trackings.activities.store', $tracking), [
        'lot_name' => 'Réseau',
        'name' => 'Installation des équipements',
        'assigned_agents' => ['Aminata Fall', 'Moussa Ndiaye'],
        'external_stakeholders' => [[
            'last_name' => 'Diop',
            'first_name' => 'Fatou',
            'email' => null,
        ]],
        'current_start_date' => now()->toDateString(),
        'current_end_date' => now()->addDay()->toDateString(),
        'planned_quantity' => 100,
        'priority' => 'normal',
    ])->assertSessionHas('success');

    $activity = $tracking->activities()->where('name', 'Installation des équipements')->firstOrFail();

    expect($activity->sort_order)->toBe(4)
        ->and($activity->unit)->toBe('pourcentage')
        ->and($activity->assigned_agents)->toBe(['Aminata Fall', 'Moussa Ndiaye'])
        ->and($activity->external_stakeholders[0]['email'])->toBeNull();
});

it('rejects work quantities above the planned quantity', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    $activity = ProjectActivity::factory()->for($tracking)->create(['planned_quantity' => 20]);

    $this->actingAs($user)->post(route('project-trackings.work-logs.store', $tracking), [
        'project_activity_id' => $activity->id,
        'started_at' => now()->subHours(2)->format('Y-m-d\TH:i'),
        'ended_at' => now()->subHour()->format('Y-m-d\TH:i'),
        'quantity_completed' => 25,
        'work_description' => 'Travaux réalisés sur le site du client.',
    ])->assertSessionHas('error');

    $this->assertDatabaseCount('work_logs', 0);
});

it('accepts a work end after its start', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    $activity = ProjectActivity::factory()->for($tracking)->create(['planned_quantity' => 20]);

    $this->actingAs($user)->post(route('project-trackings.work-logs.store', $tracking), [
        'project_activity_id' => $activity->id,
        'started_at' => now()->subHour()->format('Y-m-d\TH:i'),
        'ended_at' => now()->addHour()->format('Y-m-d\TH:i'),
        'quantity_completed' => 5,
        'work_description' => 'Travaux réalisés sur une période continue.',
    ])->assertSessionHas('success');

    $this->assertDatabaseCount('work_logs', 1);
});

it('lets the project owner delete a blocker', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    $blocker = ProjectBlocker::factory()->for($tracking)->for($user)->create();

    $this->actingAs($user)
        ->delete(route('project-blockers.destroy', $blocker))
        ->assertSessionHas('success');

    $this->assertModelMissing($blocker);
});

it('assigns an action to multiple responsible people', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();

    $this->actingAs($user)->post(route('project-trackings.actions.store', $tracking), [
        'title' => 'Finaliser la recette technique',
        'responsible_names' => ['Aminata Fall', 'Moussa Ndiaye'],
        'due_date' => now()->addWeek()->toDateString(),
        'priority' => 'high',
    ])->assertSessionHas('success');

    $action = $tracking->actions()->firstOrFail();

    expect($action->responsible_names)->toBe(['Aminata Fall', 'Moussa Ndiaye'])
        ->and($action->responsible_name)->toBe('Aminata Fall');
});

it('lets the project owner delete an action', function () {
    $user = User::factory()->create(['role' => 'user']);
    $tracking = ProjectTracking::factory()->for($user)->create();
    $action = ProjectAction::factory()->for($tracking)->for($user)->create();

    $this->actingAs($user)
        ->delete(route('project-actions.destroy', $action))
        ->assertSessionHas('success');

    $this->assertModelMissing($action);
});
