<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterventionUte;
use App\Models\Maintenance;
use App\Models\Survey;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI 1: Total Interventions
        $totalInterventions = $this->getTotalInterventions();
        $monthlyVariation = $this->getMonthlyVariation();

        // KPI 2: Taux de Validation
        $validationRate = $this->getValidationRate();

        // KPI 3: Évolution Mensuelle (12 derniers mois)
        $monthlyEvolution = $this->getMonthlyEvolution();

        // KPI 4: Répartition par Type
        $typeDistribution = $this->getTypeDistribution();

        // KPI 5: Répartition par Statut
        $statusDistribution = $this->getStatusDistribution();

        // PHASE 2: KPIs Avancés
        // KPI 6: Performance par Filiale
        $filialePerformance = $this->getFilialePerformance();

        // KPI 7: Top 10 Utilisateurs
        $topUsers = $this->getTopUsers();

        // KPI 8: Temps Moyen de Validation
        $avgValidationTime = $this->getAverageValidationTime();

        // KPI 9: Utilisateurs Actifs
        $activeUsers = $this->getActiveUsers();

        return view('admin.dashboard', compact(
            'totalInterventions',
            'monthlyVariation',
            'validationRate',
            'monthlyEvolution',
            'typeDistribution',
            'statusDistribution',
            'filialePerformance',
            'topUsers',
            'avgValidationTime',
            'activeUsers'
        ));
    }

    private function getTotalInterventions(): int
    {
        return InterventionUte::count() +
               Maintenance::count() +
               Survey::count();
    }

    private function getMonthlyVariation(): array
    {
        $thisMonth = InterventionUte::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count() +
                     Maintenance::whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year)
                         ->count() +
                     Survey::whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year)
                         ->count();

        $lastMonth = InterventionUte::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count() +
                     Maintenance::whereMonth('created_at', now()->subMonth()->month)
                         ->whereYear('created_at', now()->subMonth()->year)
                         ->count() +
                     Survey::whereMonth('created_at', now()->subMonth()->month)
                         ->whereYear('created_at', now()->subMonth()->year)
                         ->count();

        $variation = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'current' => $thisMonth,
            'previous' => $lastMonth,
            'percentage' => round($variation, 1),
        ];
    }

    private function getValidationRate(): array
    {
        $total = $this->getTotalInterventions();
        $validated = InterventionUte::where('status', 'validated')->count() +
                     Maintenance::where('status', 'validated')->count() +
                     Survey::where('status', 'validated')->count();

        $rate = $total > 0 ? ($validated / $total) * 100 : 0;

        return [
            'total' => $total,
            'validated' => $validated,
            'percentage' => round($rate, 1),
        ];
    }

    private function getMonthlyEvolution(): array
    {
        return collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'month' => $date->format('M Y'),
                'interventions' => InterventionUte::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'maintenances' => Maintenance::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'surveys' => Survey::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        })->values()->all();
    }

    private function getTypeDistribution(): array
    {
        return [
            'Interventions UTE' => InterventionUte::count(),
            'Maintenances' => Maintenance::count(),
            'Surveys' => Survey::count(),
        ];
    }

    private function getStatusDistribution(): array
    {
        return [
            'Draft' => InterventionUte::where('status', 'draft')->count() +
                       Maintenance::where('status', 'draft')->count() +
                       Survey::where('status', 'draft')->count(),
            'Pending' => InterventionUte::where('status', 'pending')->count() +
                         Maintenance::where('status', 'pending')->count() +
                         Survey::where('status', 'pending')->count(),
            'Validated' => InterventionUte::where('status', 'validated')->count() +
                           Maintenance::where('status', 'validated')->count() +
                           Survey::where('status', 'validated')->count(),
        ];
    }

    private function getFilialePerformance(): array
    {
        $filiales = ['GUT', 'CP', 'UTA', 'UA', 'UTE', 'UC'];

        return collect($filiales)->map(function ($filiale) {
            $total = InterventionUte::where('subsidiary', $filiale)->count() +
                     Maintenance::where('subsidiary', $filiale)->count() +
                     Survey::where('subsidiary', $filiale)->count();

            $validated = InterventionUte::where('subsidiary', $filiale)
                ->where('status', 'validated')->count() +
                         Maintenance::where('subsidiary', $filiale)
                             ->where('status', 'validated')->count() +
                         Survey::where('subsidiary', $filiale)
                             ->where('status', 'validated')->count();

            return [
                'filiale' => $filiale,
                'total' => $total,
                'validated' => $validated,
                'rate' => $total > 0 ? round(($validated / $total) * 100, 1) : 0,
            ];
        })->all();
    }

    private function getTopUsers(): array
    {
        return User::withCount([
            'interventionUtes',
            'maintenances',
            'surveys',
            'interventionUtes as validated_interventions_count' => fn ($q) => $q->where('status', 'validated'),
            'maintenances as validated_maintenances_count' => fn ($q) => $q->where('status', 'validated'),
            'surveys as validated_surveys_count' => fn ($q) => $q->where('status', 'validated'),
        ])
            ->get()
            ->map(function ($user) {
                $total = $user->intervention_utes_count + $user->maintenances_count + $user->surveys_count;
                $validated = $user->validated_interventions_count +
                             $user->validated_maintenances_count +
                             $user->validated_surveys_count;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'interventions' => $user->intervention_utes_count,
                    'maintenances' => $user->maintenances_count,
                    'surveys' => $user->surveys_count,
                    'total' => $total,
                    'validated' => $validated,
                    'rate' => $total > 0 ? round(($validated / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values()
            ->all();
    }

    private function getAverageValidationTime(): array
    {
        $interventions = InterventionUte::where('status', 'validated')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->first()
            ->avg_hours ?? 0;

        $maintenances = Maintenance::where('status', 'validated')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->first()
            ->avg_hours ?? 0;

        $surveys = Survey::where('status', 'validated')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->first()
            ->avg_hours ?? 0;

        $overall = ($interventions + $maintenances + $surveys) / 3;

        return [
            'hours' => round($overall, 1),
            'days' => round($overall / 24, 1),
        ];
    }

    private function getActiveUsers(): int
    {
        return User::where(function ($query) {
            $query->whereHas('interventionUtes', function ($q) {
                $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            })->orWhereHas('maintenances', function ($q) {
                $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            })->orWhereHas('surveys', function ($q) {
                $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            });
        })->count();
    }
}
