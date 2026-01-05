# KPI Dashboard - Portail Intervention

## 📊 KPIs Essentiels

### 1. INDICATEURS GLOBAUX (Cartes en haut)

#### 🔢 Total Interventions
- **Métrique** : Nombre total d'interventions/maintenances/surveys
- **Période** : Tous les temps / Mois en cours
- **Sous-métriques** :
  - Variation par rapport au mois dernier (+/- X%)
  - Objectif mensuel (si défini)

```php
// Total toutes interventions
$totalInterventions = InterventionUte::count() +
                      Maintenance::count() +
                      Survey::count();

// Ce mois
$thisMonth = InterventionUte::whereMonth('created_at', now()->month)->count() +
             Maintenance::whereMonth('created_at', now()->month)->count() +
             Survey::whereMonth('created_at', now()->month)->count();

// Variation mois dernier
$lastMonth = InterventionUte::whereMonth('created_at', now()->subMonth()->month)->count() +
             Maintenance::whereMonth('created_at', now()->subMonth()->month)->count() +
             Survey::whereMonth('created_at', now()->subMonth()->month)->count();

$variation = (($thisMonth - $lastMonth) / $lastMonth) * 100;
```

---

#### ✅ Taux de Validation
- **Métrique** : % d'interventions validées
- **Formule** : (Validées / Total) × 100
- **Code couleur** :
  - 🟢 > 80% : Excellent
  - 🟡 60-80% : Bon
  - 🔴 < 60% : À améliorer

```php
$totalDocs = InterventionUte::count() + Maintenance::count() + Survey::count();
$validatedDocs = InterventionUte::where('status', 'validated')->count() +
                 Maintenance::where('status', 'validated')->count() +
                 Survey::where('status', 'validated')->count();

$tauxValidation = ($validatedDocs / $totalDocs) * 100;
```

---

#### ⏱️ Temps Moyen de Validation
- **Métrique** : Durée moyenne entre création et validation
- **Unité** : Jours / Heures
- **Objectif** : < 3 jours

```php
$avgValidationTime = InterventionUte::where('status', 'validated')
    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
    ->first()
    ->avg_hours;

$avgDays = round($avgValidationTime / 24, 1);
```

---

#### 👥 Utilisateurs Actifs
- **Métrique** : Nombre d'utilisateurs ayant créé au moins 1 intervention ce mois
- **Sous-métrique** : Top 3 utilisateurs les plus actifs

```php
$activeUsers = User::whereHas('interventionUtes', function($q) {
    $q->whereMonth('created_at', now()->month);
})->orWhereHas('maintenances', function($q) {
    $q->whereMonth('created_at', now()->month);
})->orWhereHas('surveys', function($q) {
    $q->whereMonth('created_at', now()->month);
})->count();
```

---

### 2. GRAPHIQUES & VISUALISATIONS

#### 📈 Évolution Temporelle
**Graphique en ligne** : Nombre d'interventions par jour/semaine/mois (12 derniers mois)

```php
$monthlyData = collect(range(11, 0))->map(function($monthsAgo) {
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
});
```

---

#### 🥧 Répartition par Type
**Graphique camembert** : Interventions UTE vs Maintenances vs Surveys

```php
$typeDistribution = [
    'Interventions UTE' => InterventionUte::count(),
    'Maintenances' => Maintenance::count(),
    'Surveys' => Survey::count(),
];
```

---

#### 📊 Répartition par Statut
**Graphique en barres horizontales** : Draft / Pending / Validated

```php
$statusDistribution = [
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
```

---

#### 🏢 Performance par Filiale
**Graphique en barres** : Nombre d'interventions par filiale (GUT, CP, UTA, UA, UTE, UC)

```php
$filialeData = collect(['GUT', 'CP', 'UTA', 'UA', 'UTE', 'UC'])->map(function($filiale) {
    return [
        'filiale' => $filiale,
        'count' => InterventionUte::where('subsidiary', $filiale)->count() +
                   Maintenance::where('subsidiary', $filiale)->count() +
                   Survey::where('subsidiary', $filiale)->count(),
        'validated' => InterventionUte::where('subsidiary', $filiale)
                                      ->where('status', 'validated')->count() +
                       Maintenance::where('subsidiary', $filiale)
                                  ->where('status', 'validated')->count() +
                       Survey::where('subsidiary', $filiale)
                             ->where('status', 'validated')->count(),
    ];
});
```

---

### 3. TABLEAUX DE DONNÉES

#### 🏆 Top 10 Utilisateurs
**Colonnes** : Nom | Interventions | Maintenances | Surveys | Total | Taux Validation

```php
$topUsers = User::withCount([
    'interventionUtes',
    'maintenances',
    'surveys',
    'interventionUtes as validated_interventions_count' => fn($q) => $q->where('status', 'validated'),
    'maintenances as validated_maintenances_count' => fn($q) => $q->where('status', 'validated'),
    'surveys as validated_surveys_count' => fn($q) => $q->where('status', 'validated'),
])
->get()
->map(function($user) {
    $total = $user->intervention_utes_count + $user->maintenances_count + $user->surveys_count;
    $validated = $user->validated_interventions_count +
                 $user->validated_maintenances_count +
                 $user->validated_surveys_count;
    return [
        'name' => $user->name,
        'interventions' => $user->intervention_utes_count,
        'maintenances' => $user->maintenances_count,
        'surveys' => $user->surveys_count,
        'total' => $total,
        'taux_validation' => $total > 0 ? round(($validated / $total) * 100, 1) : 0,
    ];
})
->sortByDesc('total')
->take(10);
```

---

#### 📍 Interventions par Localisation
**Top 10 lieux** les plus fréquents

```php
$topLocations = DB::table(DB::raw('(
    SELECT location FROM intervention_utes
    UNION ALL
    SELECT location FROM maintenances
    UNION ALL
    SELECT location FROM surveys
) as all_locations'))
->select('location', DB::raw('COUNT(*) as count'))
->groupBy('location')
->orderByDesc('count')
->limit(10)
->get();
```

---

#### 🏢 Entreprises les plus servies
**Top 10 entreprises** avec le plus d'interventions

```php
$topCompanies = DB::table(DB::raw('(
    SELECT company_name FROM intervention_utes
    UNION ALL
    SELECT company_name FROM maintenances
    UNION ALL
    SELECT company_name FROM surveys
) as all_companies'))
->select('company_name', DB::raw('COUNT(*) as count'))
->groupBy('company_name')
->orderByDesc('count')
->limit(10)
->get();
```

---

### 4. KPIs AVANCÉS

#### ⚡ Durée Moyenne des Interventions
**Métrique** : Temps moyen entre start_datetime et end_datetime

```php
$avgDuration = InterventionUte::selectRaw('AVG(TIMESTAMPDIFF(HOUR, start_datetime, end_datetime)) as avg_hours')
    ->first()
    ->avg_hours;

$avgHours = round($avgDuration, 1);
```

---

#### 📝 Taux de Complétion des Observations
**Métrique** : % d'interventions avec observations remplies

```php
$withObservations = InterventionUte::whereNotNull('observations')
                                   ->where('observations', '!=', '')
                                   ->count();
$totalInterventions = InterventionUte::count();
$tauxObservations = ($withObservations / $totalInterventions) * 100;
```

---

#### 📎 Taux d'Attachements
**Métrique** : % d'interventions avec au moins 1 fichier joint

```php
$withAttachments = InterventionUte::has('attachments')->count() +
                   Maintenance::has('attachments')->count() +
                   Survey::has('attachments')->count();
$totalDocs = InterventionUte::count() + Maintenance::count() + Survey::count();
$tauxAttachments = ($withAttachments / $totalDocs) * 100;
```

---

#### 👥 Nombre Moyen d'Intervenants
**Métrique** : Moyenne d'intervenants par intervention

```php
$avgIntervenants = InterventionUte::withCount('intervenants')
    ->get()
    ->avg('intervenants_count');
```

---

#### 🔍 Diagnostics les plus fréquents (Interventions UTE)
**Top 5** : cablage, wifi, FAI, electricite, autre

```php
$topDiagnostics = InterventionUte::select('diagnostic', DB::raw('COUNT(*) as count'))
    ->groupBy('diagnostic')
    ->orderByDesc('count')
    ->get();
```

---

#### 🔧 Types d'intervention les plus fréquents
**Top 5** : changement_piece, entretien, depannage, autre

```php
$topTypes = InterventionUte::select('type', DB::raw('COUNT(*) as count'))
    ->groupBy('type')
    ->orderByDesc('count')
    ->get();
```

---

### 5. KPIs TEMPORELS

#### 📅 Interventions par Jour de la Semaine
**Graphique en barres** : Lundi à Dimanche

```php
$byDayOfWeek = InterventionUte::selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as count')
    ->groupBy('day')
    ->orderBy('day')
    ->get()
    ->mapWithKeys(function($item) {
        $days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        return [$days[$item->day - 1] => $item->count];
    });
```

---

#### ⏰ Heures de Pointe
**Graphique** : Nombre d'interventions créées par tranche horaire

```php
$byHour = InterventionUte::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();
```

---

### 6. ALERTES & NOTIFICATIONS

#### ⚠️ Interventions en Attente > 7 Jours
```php
$pendingTooLong = InterventionUte::where('status', 'pending')
    ->where('created_at', '<', now()->subDays(7))
    ->count();
```

---

#### 📝 Interventions Sans Signature
```php
$withoutSignature = InterventionUte::where('status', 'validated')
    ->doesntHave('signature')
    ->count();
```

---

## 🎨 STRUCTURE DU TABLEAU DE BORD

```
┌─────────────────────────────────────────────────────────┐
│                    HEADER / FILTRES                      │
│  [Période: Ce mois ▼] [Filiale: Toutes ▼] [Type: Tous ▼]│
└─────────────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────┬──────────────┐
│   📊 TOTAL   │  ✅ TAUX     │  ⏱️ TEMPS    │  👥 USERS    │
│  INTERVENTIONS│  VALIDATION  │  VALIDATION  │   ACTIFS     │
│     245      │    85.2%     │   2.3 jours  │     12       │
│   +15.3% 📈  │   +5.1% 📈   │   -0.5j 📉   │    +2 📈     │
└──────────────┴──────────────┴──────────────┴──────────────┘

┌─────────────────────────────┬─────────────────────────────┐
│  📈 ÉVOLUTION MENSUELLE     │  🥧 RÉPARTITION PAR TYPE    │
│  [Graphique ligne]          │  [Graphique camembert]      │
│                             │                             │
└─────────────────────────────┴─────────────────────────────┘

┌─────────────────────────────┬─────────────────────────────┐
│  📊 PAR STATUT              │  🏢 PAR FILIALE             │
│  [Graphique barres]         │  [Graphique barres]         │
│                             │                             │
└─────────────────────────────┴─────────────────────────────┘

┌───────────────────────────────────────────────────────────┐
│  🏆 TOP 10 UTILISATEURS                                    │
│  [Tableau avec tri/recherche]                             │
└───────────────────────────────────────────────────────────┘

┌──────────────────────────┬────────────────────────────────┐
│  📍 TOP LOCALISATIONS    │  🏢 TOP ENTREPRISES            │
│  [Liste]                 │  [Liste]                       │
└──────────────────────────┴────────────────────────────────┘

┌───────────────────────────────────────────────────────────┐
│  ⚠️ ALERTES                                                │
│  • 5 interventions en attente > 7 jours                    │
│  • 3 interventions validées sans signature                │
└───────────────────────────────────────────────────────────┘
```

---

## 🚀 IMPLÉMENTATION

### Créer le contrôleur
```bash
php artisan make:controller Admin/DashboardController
```

### Route
```php
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');
```

### Vue recommandée
Utiliser des librairies comme :
- **Chart.js** pour les graphiques
- **ApexCharts** (recommandé)
- **Tailwind** pour le design
- **Alpine.js** pour l'interactivité

---

## 📊 EXPORT & RAPPORTS

Ajouter des boutons pour :
- 📥 **Export Excel** (toutes les données)
- 📄 **Export PDF** (rapport mensuel)
- 📧 **Envoi par email** (rapport automatique)
- 📅 **Planification** (rapport hebdomadaire/mensuel)

---

## 🎯 PRIORISATION

### Phase 1 (Essentiel) :
1. ✅ Total Interventions
2. ✅ Taux de Validation
3. 📈 Évolution Mensuelle
4. 🥧 Répartition par Type
5. 📊 Répartition par Statut

### Phase 2 (Recommandé) :
6. 🏢 Performance par Filiale
7. 🏆 Top 10 Utilisateurs
8. ⏱️ Temps Moyen de Validation
9. 👥 Utilisateurs Actifs

### Phase 3 (Avancé) :
10. 📍 Top Localisations
11. 🏢 Top Entreprises
12. ⚡ Durée Moyenne
13. 📝 Taux de Complétion
14. ⚠️ Alertes

---

Voulez-vous que je commence à implémenter certains de ces KPIs ?
