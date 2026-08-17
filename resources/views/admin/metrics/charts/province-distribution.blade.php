@php
    $provinceMaximum = max((int) $provinceDistribution->max('total'), 1);
@endphp

<section class="metrics-chart-card" aria-labelledby="provinceDistributionTitle">
    <div class="metrics-chart-heading">
        <div>
            <h2 id="provinceDistributionTitle">Donantes por provincia</h2>
            <p>Cantidad de registros agrupados geográficamente.</p>
        </div>
        <span class="metrics-chart-badge">Todas las provincias</span>
    </div>

    <div class="horizontal-chart" aria-label="Distribución de donantes por provincia">
        @forelse ($provinceDistribution as $province)
            @php
                $total = (int) $province->total;
                $width = $total > 0 ? max(1.5, ($total / $provinceMaximum) * 100) : 0;
            @endphp
            <div class="horizontal-row" tabindex="0" aria-label="{{ $province->label }}: {{ $total }} donantes">
                <span class="province-label" title="{{ $province->label }}">{{ $province->label }}</span>
                <div class="province-track" aria-hidden="true">
                    <div class="province-track-fill" style="width: {{ number_format($width, 2, '.', '') }}%"></div>
                </div>
                <strong class="province-value">{{ $total }}</strong>
            </div>
        @empty
            <p class="metrics-empty">No existen donantes con provincia registrada.</p>
        @endforelse
    </div>
</section>
