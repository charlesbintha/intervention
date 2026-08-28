<?php

namespace App\Http\Controllers;

use App\Models\InterventionUte;
use App\Models\Maintenance;
use App\Models\ProjectTracking;
use App\Models\Survey;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'surveys' => Survey::count(),
            'maintenances' => Maintenance::count(),
            'interventions' => InterventionUte::count(),
            'project_trackings' => ProjectTracking::count(),
        ];

        return view('home', compact('stats'));
    }
}
