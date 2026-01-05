@extends('layouts.app')

@section('title', 'Tableau de Bord Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Tableau de Bord Admin</h1>
        <p class="text-gray-600 dark:text-gray-400">Vue d'ensemble des interventions, maintenances et surveys</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Interventions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Interventions</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalInterventions }}</p>
                    @if($monthlyVariation['percentage'] != 0)
                        <p class="text-sm mt-2">
                            <span class="{{ $monthlyVariation['percentage'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $monthlyVariation['percentage'] > 0 ? '+' : '' }}{{ $monthlyVariation['percentage'] }}%
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">ce mois</span>
                        </p>
                    @endif
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Taux de Validation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Taux de Validation</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $validationRate['percentage'] }}%</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        {{ $validationRate['validated'] }} / {{ $validationRate['total'] }} validées
                    </p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Ce Mois -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ce Mois</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $monthlyVariation['current'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        {{ $monthlyVariation['previous'] }} le mois dernier
                    </p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Utilisateurs Actifs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Utilisateurs Actifs</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $activeUsers }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        ce mois
                    </p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
        <!-- Temps Moyen de Validation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Temps Moyen de Validation</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $avgValidationTime['days'] }} jours</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        {{ $avgValidationTime['hours'] }} heures
                    </p>
                </div>
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Évolution Mensuelle -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Évolution Mensuelle</h2>
            <div id="monthlyEvolutionChart"></div>
        </div>

        <!-- Répartition par Type -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Répartition par Type</h2>
            <div id="typeDistributionChart"></div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Répartition par Statut</h2>
        <div id="statusDistributionChart"></div>
    </div>

    <!-- Phase 2 Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Performance par Filiale -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance par Filiale</h2>
            <div id="filialePerformanceChart"></div>
        </div>

        <!-- Top 10 Utilisateurs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top 10 Utilisateurs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nom</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Taux</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($topUsers as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user['total'] }}</span>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    I:{{ $user['interventions'] }} M:{{ $user['maintenances'] }} S:{{ $user['surveys'] }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($user['rate'] >= 80) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                    @elseif($user['rate'] >= 60) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                    @endif">
                                    {{ $user['rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Évolution Mensuelle
    var monthlyEvolutionOptions = {
        series: [{
            name: 'Interventions UTE',
            data: @json(array_column($monthlyEvolution, 'interventions'))
        }, {
            name: 'Maintenances',
            data: @json(array_column($monthlyEvolution, 'maintenances'))
        }, {
            name: 'Surveys',
            data: @json(array_column($monthlyEvolution, 'surveys'))
        }],
        chart: {
            type: 'line',
            height: 350,
            toolbar: {
                show: true
            }
        },
        colors: ['#0099CC', '#FF8C00', '#10B981'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: @json(array_column($monthlyEvolution, 'month'))
        },
        yaxis: {
            title: {
                text: 'Nombre'
            }
        },
        legend: {
            position: 'top'
        },
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            }
        }
    };

    var monthlyEvolutionChart = new ApexCharts(document.querySelector("#monthlyEvolutionChart"), monthlyEvolutionOptions);
    monthlyEvolutionChart.render();

    // Répartition par Type
    var typeDistributionOptions = {
        series: @json(array_values($typeDistribution)),
        chart: {
            type: 'donut',
            height: 350
        },
        labels: @json(array_keys($typeDistribution)),
        colors: ['#0099CC', '#FF8C00', '#10B981'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            show: true
                        },
                        value: {
                            show: true,
                            fontSize: '24px',
                            fontWeight: 'bold'
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        }
    };

    var typeDistributionChart = new ApexCharts(document.querySelector("#typeDistributionChart"), typeDistributionOptions);
    typeDistributionChart.render();

    // Répartition par Statut
    var statusDistributionOptions = {
        series: [{
            name: 'Nombre',
            data: @json(array_values($statusDistribution))
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: true
            }
        },
        colors: ['#6B7280', '#F59E0B', '#10B981'],
        plotOptions: {
            bar: {
                horizontal: true,
                distributed: true,
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: true
        },
        xaxis: {
            categories: @json(array_keys($statusDistribution)),
            title: {
                text: 'Nombre d\'interventions'
            }
        },
        yaxis: {
            title: {
                text: 'Statut'
            }
        },
        legend: {
            show: false
        }
    };

    var statusDistributionChart = new ApexCharts(document.querySelector("#statusDistributionChart"), statusDistributionOptions);
    statusDistributionChart.render();

    // Performance par Filiale
    var filialePerformanceOptions = {
        series: [{
            name: 'Total',
            data: @json(array_column($filialePerformance, 'total'))
        }, {
            name: 'Validées',
            data: @json(array_column($filialePerformance, 'validated'))
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: true
            }
        },
        colors: ['#0099CC', '#10B981'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: @json(array_column($filialePerformance, 'filiale')),
            title: {
                text: 'Filiale'
            }
        },
        yaxis: {
            title: {
                text: 'Nombre d\'interventions'
            }
        },
        fill: {
            opacity: 1
        },
        legend: {
            position: 'top'
        },
        tooltip: {
            y: {
                formatter: function (val, opts) {
                    const filialeData = @json($filialePerformance);
                    const index = opts.dataPointIndex;
                    const rate = filialeData[index].rate;
                    return val + ' (' + rate + '%)';
                }
            }
        }
    };

    var filialePerformanceChart = new ApexCharts(document.querySelector("#filialePerformanceChart"), filialePerformanceOptions);
    filialePerformanceChart.render();
</script>
@endsection
