@php
    $chartWidth = 920;
    $chartHeight = 320;
    $margin = ['top' => 36, 'right' => 28, 'bottom' => 48, 'left' => 48];
    $plotWidth = $chartWidth - $margin['left'] - $margin['right'];
    $plotHeight = $chartHeight - $margin['top'] - $margin['bottom'];
    $maximum = max((int) $growth->max('total'), 1);
    $points = $growth->values()->map(function (array $month, int $index) use ($margin, $plotWidth, $plotHeight, $maximum): array {
        return array_merge($month, [
            'x' => $margin['left'] + ($index * $plotWidth / 11),
            'y' => $margin['top'] + $plotHeight - ($month['total'] / $maximum * $plotHeight),
        ]);
    });
    $linePath = $points->map(fn (array $point, int $index): string => ($index === 0 ? 'M' : 'L').' '.$point['x'].' '.$point['y'])->implode(' ');
    $areaPath = $linePath.' L '.$points->last()['x'].' '.($margin['top'] + $plotHeight).' L '.$points->first()['x'].' '.($margin['top'] + $plotHeight).' Z';
@endphp

<section class="metrics-chart-card metrics-chart-card-wide" aria-labelledby="growthTitle">
    <div class="metrics-chart-heading">
        <div>
            <h2 id="growthTitle">Crecimiento acumulado de donantes</h2>
            <p>Evolución mensual del total de donantes activos durante los últimos 12 meses.</p>
        </div>
        <span class="metrics-chart-badge">Hasta el mes actual</span>
    </div>

    <div class="growth-chart" aria-label="Gráfica de crecimiento acumulado mensual de donantes">
        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-labelledby="growthSvgTitle growthSvgDescription">
            <title id="growthSvgTitle">Crecimiento acumulado de donantes activos durante los últimos doce meses</title>
            <desc id="growthSvgDescription">La línea presenta el total acumulado de donantes activos y cada punto indica los activos registrados en el mes.</desc>
            <defs><linearGradient id="growthGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#159a70" stop-opacity="0.32" /><stop offset="100%" stop-color="#159a70" stop-opacity="0.03" /></linearGradient></defs>

            @foreach([0, .25, .5, .75, 1] as $level)
                @php($gridY = $margin['top'] + $plotHeight - ($level * $plotHeight))
                <line class="growth-grid-line" x1="{{ $margin['left'] }}" y1="{{ $gridY }}" x2="{{ $chartWidth - $margin['right'] }}" y2="{{ $gridY }}" />
                <text class="growth-axis-label" x="{{ $margin['left'] - 9 }}" y="{{ $gridY + 4 }}">{{ round($maximum * $level) }}</text>
            @endforeach

            <path class="growth-area" d="{{ $areaPath }}" /><path class="growth-line" d="{{ $linePath }}" />
            @foreach($points as $point)
                <g class="growth-data-point" tabindex="0" aria-label="{{ $point['label'] }}: total {{ $point['total'] }}, altas {{ $point['registrations'] }}">
                    <circle class="growth-point" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" />
                    <text class="growth-value" x="{{ $point['x'] }}" y="{{ $point['y'] - 13 }}">{{ $point['total'] }}</text>
                    <text class="growth-increment" x="{{ $point['x'] }}" y="{{ $point['y'] + 21 }}">+{{ $point['registrations'] }}</text>
                    <text class="growth-label" x="{{ $point['x'] }}" y="{{ $chartHeight - 17 }}">{{ $point['label'] }}</text>
                    <title>{{ ucfirst($point['label']) }} {{ substr($point['period'], 0, 4) }}: {{ $point['total'] }} acumulados, +{{ $point['registrations'] }} altas</title>
                </g>
            @endforeach
        </svg>
    </div>
</section>
