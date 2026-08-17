@php
    $ageMaximum = max((int) $ageDistribution->max('total'), 1);
@endphp

<section class="metrics-chart-card" aria-labelledby="ageDistributionTitle">
    <div class="metrics-chart-heading">
        <div>
            <h2 id="ageDistributionTitle">Donantes por edad</h2>
            <p>Distribución calculada desde la fecha de nacimiento.</p>
        </div>
        <span class="metrics-chart-badge">Rangos de edad</span>
    </div>

    <div class="vertical-chart" aria-label="Distribución de donantes por rangos de edad">
        @foreach ($ageDistribution as $range)
            @php
                $total = (int) $range['total'];
                $height = $total > 0 ? max(3, ($total / $ageMaximum) * 82) : 1;
            @endphp
            <div class="bar-column" tabindex="0" aria-label="{{ $range['label'] }} años: {{ $total }} donantes">
                <span class="bar-value">{{ $total }}</span>
                <div class="bar-fill" style="height: {{ number_format($height, 2, '.', '') }}%"></div>
                <span class="bar-label">{{ $range['label'] }}</span>
            </div>
        @endforeach
    </div>
</section>
