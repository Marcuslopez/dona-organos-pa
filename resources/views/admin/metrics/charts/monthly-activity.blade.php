@php($activityMaximum = max((int) $monthlyActivity->max(fn (array $month): int => max($month['highs'], $month['lows'])), 1))

<section class="metrics-chart-card metrics-chart-card-wide" aria-labelledby="monthlyActivityTitle">
    <div class="metrics-chart-heading">
        <div>
            <h2 id="monthlyActivityTitle">Altas y bajas de los últimos 12 meses</h2>
            <p>Donantes registrados cada mes, clasificados según su estado actual.</p>
        </div>
        <span class="metrics-chart-badge">Últimos 12 meses</span>
    </div>

    <div class="month-legend" aria-label="Leyenda de la gráfica">
        <span><i class="is-high"></i>Altas</span>
        <span><i class="is-low"></i>Bajas</span>
    </div>

    <div class="month-chart" role="img" aria-label="Altas y bajas mensuales durante los últimos doce meses">
        @foreach($monthlyActivity as $month)
            <div class="month-column" tabindex="0" aria-label="{{ $month['label'] }}: {{ $month['highs'] }} altas y {{ $month['lows'] }} bajas">
                <div class="month-bars">
                    <div class="month-series is-high">
                        <span class="month-value">{{ $month['highs'] }}</span>
                        <span class="month-fill" style="height: {{ max(2, ($month['highs'] / $activityMaximum) * 100) }}%"></span>
                    </div>
                    <div class="month-series is-low">
                        <span class="month-value">{{ $month['lows'] }}</span>
                        <span class="month-fill" style="height: {{ max(2, ($month['lows'] / $activityMaximum) * 100) }}%"></span>
                    </div>
                </div>
                <span class="month-label">{{ $month['label'] }}</span>
            </div>
        @endforeach
    </div>
</section>
