<?php

namespace App\Services;

use App\Models\ProjectActivity;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MicrosoftGraphService
{
    public function isConfigured(): bool
    {
        return filled(config('services.microsoft_graph.tenant_id'))
            && filled(config('services.microsoft_graph.client_id'))
            && filled(config('services.microsoft_graph.client_secret'));
    }

    /** @return Collection<int, array{id: string, name: string, email: string}> */
    public function getGroupMembers(?string $groupId): Collection
    {
        if (! $this->isConfigured() || blank($groupId)) {
            return collect();
        }

        return Cache::remember("ms-graph.group-members.{$groupId}", now()->addMinutes(5), function () use ($groupId): Collection {
            $members = collect();
            $nextUrl = '/groups/'.rawurlencode($groupId).'/members/microsoft.graph.user?$select=id,displayName,mail,userPrincipalName';

            do {
                $response = str_starts_with($nextUrl, 'https://')
                    ? Http::withToken($this->accessToken())->acceptJson()->get($nextUrl)
                    : $this->graph()->get($nextUrl);

                $this->throwForGraphError($response->successful(), $response->json(), 'Impossible de récupérer les membres du projet.');

                $members->push(...collect($response->json('value', []))->map(function (array $member): array {
                    return [
                        'id' => (string) $member['id'],
                        'name' => (string) ($member['displayName'] ?? $member['mail'] ?? $member['userPrincipalName']),
                        'email' => mb_strtolower((string) ($member['mail'] ?? $member['userPrincipalName'] ?? '')),
                    ];
                })->filter(fn (array $member): bool => $member['email'] !== ''));

                $nextUrl = $response->json('@odata.nextLink');
            } while (filled($nextUrl));

            return $members->unique('email')->sortBy('name')->values();
        });
    }

    public function syncActivity(ProjectActivity $activity): string
    {
        $tracking = $activity->projectTracking;

        if (blank($tracking->ms_group_id) || blank($tracking->ms_plan_id)) {
            throw new RuntimeException('Ce projet ne possède pas de groupe et de plan Microsoft Planner valides.');
        }

        $users = collect($activity->assigned_agent_emails ?? [])
            ->map(fn (string $email): array => $this->getUserByEmail($email))
            ->values();

        $memberIds = $this->getGroupMembers($tracking->ms_group_id)->pluck('id');
        foreach ($users->whereNotIn('id', $memberIds) as $user) {
            $response = $this->graph()->post('/groups/'.rawurlencode($tracking->ms_group_id).'/members/$ref', [
                '@odata.id' => 'https://graph.microsoft.com/v1.0/directoryObjects/'.$user['id'],
            ]);

            $isAlreadyMember = $response->status() === 400
                && Str::contains((string) data_get($response->json(), 'error.message'), ['already exist', 'déjà']);

            if (! $response->successful() && ! $isAlreadyMember) {
                $this->throwForGraphError(false, $response->json(), "Impossible d’ajouter {$user['name']} au groupe Microsoft 365.");
            }
        }

        Cache::forget("ms-graph.group-members.{$tracking->ms_group_id}");

        $assignments = $users->mapWithKeys(fn (array $user): array => [
            $user['id'] => [
                '@odata.type' => '#microsoft.graph.plannerAssignment',
                'orderHint' => ' !',
            ],
        ])->all();

        $payload = [
            'title' => $activity->name,
            'startDateTime' => Carbon::parse($activity->current_start_date)->startOfDay()->utc()->toIso8601String(),
            'dueDateTime' => Carbon::parse($activity->current_end_date)->endOfDay()->utc()->toIso8601String(),
            'assignments' => $assignments,
        ];

        if (blank($activity->ms_planner_task_id)) {
            $response = $this->graph()->post('/planner/tasks', [
                ...$payload,
                'planId' => $tracking->ms_plan_id,
                ...filled($tracking->ms_bucket_id) ? ['bucketId' => $tracking->ms_bucket_id] : [],
            ]);
            $this->throwForGraphError($response->created(), $response->json(), 'Impossible de créer la tâche Planner.');

            return (string) $response->json('id');
        }

        $taskPath = '/planner/tasks/'.rawurlencode($activity->ms_planner_task_id);
        $task = $this->graph()->get($taskPath);
        $this->throwForGraphError($task->successful(), $task->json(), 'La tâche Planner liée est introuvable.');

        $removedAssignments = collect(array_keys($task->json('assignments', [])))
            ->diff($users->pluck('id'))
            ->mapWithKeys(fn (string $userId): array => [$userId => null])
            ->all();
        $payload['assignments'] = [...$removedAssignments, ...$assignments];
        $etag = (string) (($task->json()['@odata.etag'] ?? null) ?? $task->header('ETag'));

        $response = $this->graph()
            ->withHeaders(['If-Match' => $etag])
            ->patch($taskPath, $payload);
        $this->throwForGraphError($response->successful(), $response->json(), 'Impossible de mettre à jour la tâche Planner.');

        return $activity->ms_planner_task_id;
    }

    public function deletePlannerTask(string $taskId): void
    {
        $taskPath = '/planner/tasks/'.rawurlencode($taskId);
        $task = $this->graph()->get($taskPath);

        if ($task->notFound()) {
            return;
        }

        $this->throwForGraphError($task->successful(), $task->json(), 'La tâche Planner liée est introuvable.');
        $etag = (string) (($task->json()['@odata.etag'] ?? null) ?? $task->header('ETag'));

        if (blank($etag)) {
            throw new RuntimeException('Microsoft Planner n’a pas retourné la version de la tâche à supprimer.');
        }

        $response = $this->graph()
            ->withHeaders(['If-Match' => $etag])
            ->delete($taskPath);

        if ($response->notFound()) {
            return;
        }

        $this->throwForGraphError($response->successful(), $response->json(), 'Impossible de supprimer la tâche Planner.');
    }

    /** @return array{id: string, name: string, email: string} */
    private function getUserByEmail(string $email): array
    {
        $normalizedEmail = mb_strtolower(trim($email));
        $response = $this->graph()->get('/users/'.rawurlencode($normalizedEmail).'?'.http_build_query([
            '$select' => 'id,displayName,mail,userPrincipalName',
        ]));
        $this->throwForGraphError($response->successful(), $response->json(), "Le compte Microsoft {$normalizedEmail} est introuvable.");

        return [
            'id' => (string) $response->json('id'),
            'name' => (string) ($response->json('displayName') ?? $normalizedEmail),
            'email' => $normalizedEmail,
        ];
    }

    private function graph(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->baseUrl('https://graph.microsoft.com/v1.0')
            ->timeout(20);
    }

    private function accessToken(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('La connexion Microsoft Graph n’est pas configurée.');
        }

        return Cache::remember('ms-graph.access-token', now()->addMinutes(50), function (): string {
            $response = Http::asForm()->timeout(20)->post(
                'https://login.microsoftonline.com/'.rawurlencode((string) config('services.microsoft_graph.tenant_id')).'/oauth2/v2.0/token',
                [
                    'client_id' => config('services.microsoft_graph.client_id'),
                    'client_secret' => config('services.microsoft_graph.client_secret'),
                    'scope' => config('services.microsoft_graph.scope'),
                    'grant_type' => 'client_credentials',
                ],
            );

            if (! $response->successful() || blank($response->json('access_token'))) {
                throw new RuntimeException('Microsoft Graph a refusé l’authentification de l’application.');
            }

            return (string) $response->json('access_token');
        });
    }

    /** @param array<string, mixed>|null $payload */
    private function throwForGraphError(bool $successful, ?array $payload, string $fallbackMessage): void
    {
        if ($successful) {
            return;
        }

        $message = data_get($payload, 'error.message');

        throw new RuntimeException(filled($message) ? "{$fallbackMessage} {$message}" : $fallbackMessage);
    }
}
