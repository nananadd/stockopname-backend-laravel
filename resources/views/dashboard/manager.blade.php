@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark">
            <i class="fas fa-chart-line text-primary me-2"></i>Dashboard Evaluasi Eksekutif
        </h3>
        <p class="text-muted">Ringkasan analitik Cycle Counting & Pergerakan Gudang</p>
    </div>
        <div>
            <span class="text-primary fw-medium" style="font-size: 1.1rem;">
                <i class="fas fa-clock text-primary me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
            </span>
        </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-sigma-black text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase mb-2 text-white-50">Total Rak Gudang</h6>
                <h2 class="display-6 fw-bold mb-0">{{ $totalRacks }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-sigma-magenta text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase mb-2 text-white-50">Total Master Barang</h6>
                <h2 class="display-6 fw-bold mb-0">{{ $totalItems }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4" style="border-color: var(--sigma-black) !important;">
            <div class="card-body">
                <h6 class="text-uppercase mb-2 text-muted">Total Cycle Count</h6>
                <h2 class="display-6 fw-bold mb-0 text-dark">{{ $totalCycleCounts }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4" style="border-color: var(--sigma-magenta) !important;">
            <div class="card-body">
                <h6 class="text-uppercase mb-2 text-muted">Aktivitas Bulan Ini</h6>
                <h2 class="display-6 fw-bold mb-0 text-dark">{{ $countsThisMonth }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark">Tren Cycle Counting (7 Hari Terakhir)</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark">Rasio Akurasi Stok (Variance)</h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="varianceChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const ctxTrend = document.getElementById('trendChart').getContext('2d');

    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: @json($trendDates),
            datasets: [{
                label: 'Jumlah Cycle Count Harian',
                data: @json($trendData),
                borderColor: '#f31a6b',
                backgroundColor: 'rgba(243, 26, 107, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#111111',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const ctxVariance = document.getElementById('varianceChart').getContext('2d');

    new Chart(ctxVariance, {
        type: 'doughnut',
        data: {
            labels: ['Stok Sesuai', 'Stok Selisih (Kurang/Lebih)'],
            datasets: [{
                data: [
                    {{ $varianceSesuai ?? 0 }},
                    {{ $varianceSelisih ?? 0 }}
                ],
                backgroundColor: [
                    '#111111',
                    '#f31a6b'
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

});
</script>
@endsection