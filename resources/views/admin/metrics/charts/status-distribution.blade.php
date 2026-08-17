@php
    $total = (int) $statusSummary['total'];
    $active = (int) $statusSummary['active'];
    $withdrawn = (int) $statusSummary['withdrawn'];
    $activePercentage = $total > 0 ? ($active / $total) * 100 : 0;
    $withdrawnPercentage = $total > 0 ? ($withdrawn / $total) * 100 : 0;
@endphp

<section class="metrics-chart-card metrics-chart-card-wide" aria-labelledby="statusDistributionTitle">
    <div class="metrics-chart-heading">
        <div>
            <h2 id="statusDistributionTitle">Altas y bajas de donantes</h2>
            <p>Comparación del estado actual de todos los donantes registrados.</p>
        </div>
        <span class="metrics-chart-badge">Estado actual</span>
    </div>

    <div class="status-chart" aria-label="{{ $active }} donantes activos y {{ $withdrawn }} donantes dados de baja">
        <div
            class="status-donut"
            style="--active-percentage: {{ number_format($activePercentage, 4, '.', '') }}%;"
            role="img"
            aria-label="Activos {{ number_format($activePercentage, 1) }}%, bajas {{ number_format($withdrawnPercentage, 1) }}%"
        >
            <div class="status-total">
                <strong>{{ $total }}</strong>
                <span>donantes</span>
            </div>
        </div>

        <div class="status-legend" aria-label="Detalle por estado">
            <div class="status-item">
                <span class="status-dot is-active" aria-hidden="true"></span>
                <span class="status-label">Activos</span>
                <strong class="status-value">{{ $active }}</strong>
                <span class="status-percentage">{{ number_format($activePercentage, 1) }}%</span>
            </div>
            <div class="status-item">
                <span class="status-dot is-withdrawn" aria-hidden="true"></span>
                <span class="status-label">Bajas</span>
                <strong class="status-value">{{ $withdrawn }}</strong>
                <span class="status-percentage">{{ number_format($withdrawnPercentage, 1) }}%</span>
            </div>
        </div>
    </div>
</section>
