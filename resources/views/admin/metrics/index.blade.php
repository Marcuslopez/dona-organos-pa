@extends('layouts.app')

@section('title', 'Métricas del sistema | DONA ÓRGANOS PANAMÁ')

@section('content')
<div class="admin-app metrics-app">
    <header class="admin-header metrics-header">
        <div class="container-fluid admin-header-inner">
            <a class="admin-logo" href="{{ route('admin.metrics.index') }}">
                <small>DONA ÓRGANOS PANAMÁ</small>
                <span>Métricas del sistema</span>
            </a>
            <a class="btn btn-outline-light rounded-pill" href="{{ route('admin.dashboard') }}">← Volver al dashboard</a>
        </div>
    </header>

    <main class="metrics-main">
        <div class="metrics-page-heading">
            <span>Resumen estadístico</span>
            <h1>Métricas de donantes</h1>
            <p>Información calculada con todos los registros disponibles en el sistema.</p>
        </div>

        <div class="metrics-grid">
            @include('admin.metrics.charts.cumulative-growth', ['growth' => $growth])
            @include('admin.metrics.charts.monthly-activity', ['monthlyActivity' => $monthlyActivity])
            @include('admin.metrics.charts.status-distribution', ['statusSummary' => $statusSummary])
            @include('admin.metrics.charts.age-distribution', ['ageDistribution' => $ageDistribution])
            @include('admin.metrics.charts.province-distribution', ['provinceDistribution' => $provinceDistribution])
        </div>
    </main>
</div>
@endsection
