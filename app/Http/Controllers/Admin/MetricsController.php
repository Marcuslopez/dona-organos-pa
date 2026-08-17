<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMetricsService;
use Illuminate\View\View;

class MetricsController extends Controller
{
    public function index(AdminMetricsService $metrics): View
    {
        return view('admin.metrics.index', [
            'growth' => $metrics->cumulativeGrowthLast12Months(),
            'monthlyActivity' => $metrics->registrationsByCurrentStatusLast12Months(),
            'statusSummary' => $metrics->summary(),
            'ageDistribution' => $metrics->ageDistribution(),
            'provinceDistribution' => $metrics->provinceDistribution(),
        ]);
    }
}
