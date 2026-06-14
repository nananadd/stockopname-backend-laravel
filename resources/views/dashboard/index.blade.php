@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h3 class="fw-bold text-primary">
            <i class="fas fa-chart-line me-2"></i>Dashboard
        </h3>
        <p class="text-muted mb-0">PT. SIGMA BERKAT SEJATI — STOCK OPNAME SYSTEM</p>
    </div>
    <span class="text-primary fw-medium" style="font-size: 1.1rem;">
        <i class="fas fa-clock text-primary me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </span>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between p-4 bg-sigma-black rounded">
                <div>
                    <h6 class="text-uppercase mb-2 fw-semibold" style="letter-spacing: 1px; font-size: 0.8rem;">Total Rak</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ $rackCount }}</h2>
                </div>
                <i class="fas fa-pallet fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between p-4 bg-sigma-magenta rounded">
                <div>
                    <h6 class="text-uppercase mb-2 fw-semibold" style="letter-spacing: 1px; font-size: 0.8rem;">Total Item Master</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ $itemCount }}</h2>
                </div>
                <i class="fas fa-box-open fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between p-4 rounded text-white" style="background-color: #f57c00;">
                <div>
                    <h6 class="text-uppercase mb-2 fw-semibold" style="letter-spacing: 1px; font-size: 0.8rem;">Butuh Review</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ $pendingReviewCount }}</h2>
                </div>
                <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between p-4 rounded text-white" style="background-color: #2e7d32;">
                <div>
                    <h6 class="text-uppercase mb-2 fw-semibold" style="letter-spacing: 1px; font-size: 0.8rem;">Disetujui</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ $approvedCount }}</h2>
                </div>
                <i class="fas fa-check-double fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-sigma border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>Laporan Cycle Count Terbaru</h5>
                <a href="{{ route('cycle.index') }}" class="btn btn-sm btn-sigma-accent">Lihat Semua Data</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Lokasi Rak</th>
                                <th>Tanggal Hitung</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCycles as $cycle)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $cycle->rack->code ?? 'Rak Dihapus' }}</td>
                                <td>{{ \Carbon\Carbon::parse($cycle->started_at)->format('d M Y, H:i') }}</td>
                                <td>
                                    @if($cycle->status == 'draft')
                                        <span class="badge bg-secondary">
                                            Proses Hitung
                                        </span>

                                    @elseif($cycle->status == 'submitted')
                                        <span class="badge bg-warning text-dark">
                                            Menunggu Review
                                        </span>

                                    @elseif($cycle->status == 'reviewed')
                                        <span class="badge bg-primary">
                                            Direview
                                        </span>

                                    @elseif($cycle->status == 'approved')
                                        <span class="badge bg-success">
                                            Disetujui
                                        </span>

                                    @else
                                        <span class="badge bg-dark">
                                            {{ strtoupper($cycle->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('cycle.show', $cycle->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-search me-1"></i> Cek Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Belum ada aktivitas hitung stok yang masuk.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection