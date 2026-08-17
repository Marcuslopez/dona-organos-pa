<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminMetricsService
{
    public function cumulativeGrowthLast12Months(): Collection
    {
        $timezone = config('app.timezone');
        $now = CarbonImmutable::now($timezone);
        $lastMonth = $now->startOfMonth();
        $firstMonth = $lastMonth->subMonths(11);
        $periodEnd = $now;
        $baseline = DB::table('donors')
            ->where('status', 'active')
            ->where('registered_at', '<', $firstMonth)
            ->count();

        $monthlyTotals = DB::table('donors')
            ->where('status', 'active')
            ->whereBetween('registered_at', [$firstMonth, $periodEnd])
            ->selectRaw($this->monthExpression('registered_at').' as period, COUNT(*) as total')
            ->groupBy('period')
            ->pluck('total', 'period');

        $accumulated = $baseline;
        $monthNames = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        return collect(range(0, 11))->map(function (int $offset) use ($firstMonth, $monthlyTotals, &$accumulated, $monthNames): array {
            $month = $firstMonth->addMonths($offset);
            $registrations = (int) ($monthlyTotals[$month->format('Y-m')] ?? 0);
            $accumulated += $registrations;

            return [
                'period' => $month->format('Y-m'),
                'label' => $monthNames[$month->month - 1],
                'registrations' => $registrations,
                'total' => $accumulated,
            ];
        });
    }

    public function registrationsByCurrentStatusLast12Months(): Collection
    {
        $timezone = config('app.timezone');
        $now = CarbonImmutable::now($timezone);
        $lastMonth = $now->startOfMonth();
        $firstMonth = $lastMonth->subMonths(11);
        $periodEnd = $now;

        $registrationsByStatus = DB::table('donors')
            ->whereBetween('registered_at', [$firstMonth, $periodEnd])
            ->whereIn('status', ['active', 'withdrawn'])
            ->selectRaw($this->monthExpression('registered_at').' as period, status, COUNT(*) as total')
            ->groupBy('period', 'status')->get()
            ->keyBy(fn (object $row): string => $row->period.'|'.$row->status);

        $monthNames = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        return collect(range(0, 11))->map(function (int $offset) use ($firstMonth, $registrationsByStatus, $monthNames): array {
            $month = $firstMonth->addMonths($offset);
            $period = $month->format('Y-m');
            $active = (int) ($registrationsByStatus[$period.'|active']->total ?? 0);
            $withdrawn = (int) ($registrationsByStatus[$period.'|withdrawn']->total ?? 0);

            return [
                'period' => $period,
                'label' => $monthNames[$month->month - 1],
                'highs' => $active,
                'lows' => $withdrawn,
                'total' => $active + $withdrawn,
            ];
        });
    }

    public function summary(array $filters = []): array
    {
        $query = $this->donors($filters);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('donors.status', 'active')->count(),
            'withdrawn' => (clone $query)->where('donors.status', 'withdrawn')->count(),
        ];
    }

    public function registrationsByMonth(array $filters = []): Collection
    {
        return $this->donors($filters)
            ->selectRaw($this->monthExpression('donors.registered_at').' as period, COUNT(*) as total')
            ->groupBy('period')->orderBy('period')->get();
    }

    public function ageDistribution(array $filters = []): Collection
    {
        $birthDates = $this->donors($filters)->pluck('donors.birth_date');
        $today = CarbonImmutable::today(config('app.timezone'));
        $ranges = ['18-29' => 0, '30-39' => 0, '40-49' => 0, '50-59' => 0, '60-69' => 0, '70+' => 0];

        foreach ($birthDates as $birthDate) {
            $age = CarbonImmutable::parse($birthDate)->diffInYears($today);
            $key = match (true) {
                $age < 30 => '18-29', $age < 40 => '30-39', $age < 50 => '40-49',
                $age < 60 => '50-59', $age < 70 => '60-69', default => '70+',
            };
            $ranges[$key]++;
        }

        return collect($ranges)->map(fn (int $total, string $label): array => compact('label', 'total'))->values();
    }

    public function genderDistribution(array $filters = []): Collection
    {
        return $this->donors($filters)
            ->join('genders', 'genders.id', '=', 'donors.gender_id')
            ->selectRaw('genders.name as label, COUNT(*) as total')
            ->groupBy('genders.id', 'genders.name')->orderByDesc('total')->get();
    }

    public function provinceDistribution(array $filters = []): Collection
    {
        return $this->donors($filters)
            ->join('provinces', 'provinces.id', '=', 'donors.province_id')
            ->selectRaw('provinces.name as label, COUNT(*) as total')
            ->groupBy('provinces.id', 'provinces.name')->orderByDesc('total')->get();
    }

    public function donationScopeDistribution(array $filters = []): Collection
    {
        return $this->donors($filters)
            ->join('donation_preferences', 'donation_preferences.donor_id', '=', 'donors.id')
            ->join('donation_scopes', 'donation_scopes.id', '=', 'donation_preferences.donation_scope_id')
            ->selectRaw('donation_scopes.name as label, COUNT(*) as total')
            ->groupBy('donation_scopes.id', 'donation_scopes.name')->orderByDesc('total')->get();
    }

    public function statusEventsByMonth(array $filters = []): Collection
    {
        $query = DB::table('donor_status_history')
            ->join('donors', 'donors.id', '=', 'donor_status_history.donor_id');

        $this->applyDateFilters($query, $filters, 'donor_status_history.changed_at');

        return $query
            ->selectRaw($this->monthExpression('donor_status_history.changed_at').' as period, donor_status_history.new_status as status, COUNT(*) as total')
            ->groupBy('period', 'donor_status_history.new_status')->orderBy('period')->get();
    }

    public function contactCoverage(array $filters = []): Collection
    {
        $counts = $this->donors($filters)
            ->leftJoin('donor_contacts', 'donor_contacts.donor_id', '=', 'donors.id')
            ->selectRaw('donors.id, COUNT(donor_contacts.id) as contacts')
            ->groupBy('donors.id')->pluck('contacts');

        return collect([
            ['label' => 'Un contacto', 'total' => $counts->filter(fn ($count) => (int) $count === 1)->count()],
            ['label' => 'Dos contactos', 'total' => $counts->filter(fn ($count) => (int) $count >= 2)->count()],
        ]);
    }

    private function donors(array $filters): Builder
    {
        $query = DB::table('donors');
        $this->applyDateFilters($query, $filters, 'donors.registered_at');

        return $query
            ->when(isset($filters['province_id']), fn (Builder $query) => $query->where('donors.province_id', $filters['province_id']))
            ->when(isset($filters['gender_id']), fn (Builder $query) => $query->where('donors.gender_id', $filters['gender_id']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('donors.status', $filters['status']));
    }

    private function applyDateFilters(Builder $query, array $filters, string $column): void
    {
        $query
            ->when(! empty($filters['from']), fn (Builder $query) => $query->whereDate($column, '>=', $filters['from']))
            ->when(! empty($filters['to']), fn (Builder $query) => $query->whereDate($column, '<=', $filters['to']));
    }

    private function monthExpression(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
