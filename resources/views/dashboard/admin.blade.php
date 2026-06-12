@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="fas fa-user-shield text-primary me-2"></i>Pusat Kendali Admin
            </h3>
            <p class="text-muted mb-0">PT. SIGMA BERKAT SEJATI — STOCK OPNAME SYSTEM</p>
        </div>
        <div>
            <span class="text-primary fw-medium" style="font-size: 1.1rem;">
                <i class="fas fa-clock text-primary me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
            </span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-left: 4px solid var(--sigma-magenta) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-2">Total Master Barang (SKU)</h6>
                        <h2 class="fw-bold text-dark mb-0">{{ $totalItems ?? 0 }}</h2>
                    </div>
                    <div class="bg-light rounded p-3 text-primary">
                        <i class="fas fa-cubes fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('items.index') }}" class="text-decoration-none small text-primary fw-bold">
                        Kelola Barang <i class="fas fa-arrow-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-left: 4px solid #0d6efd !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-2">Total Lokasi Rak</h6>
                        <h2 class="fw-bold text-dark mb-0">{{ $totalRacks ?? 0 }}</h2>
                    </div>
                    <div class="bg-light rounded p-3 text-primary">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('racks.index') }}" class="text-decoration-none small text-primary fw-bold">
                        Kelola Rak & Gudang <i class="fas fa-arrow-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-left: 4px solid #198754 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-2">Total Pengguna Sistem</h6>
                        <h2 class="fw-bold text-dark mb-0">{{ $totalUsers ?? 0 }}</h2>
                    </div>
                    <div class="bg-light rounded p-3 text-success">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('users.index') }}" class="text-decoration-none small text-success fw-bold">
                        Kelola Hak Akses & Staf <i class="fas fa-arrow-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-light rounded-circle me-3 text-primary">
                                <i class="fas fa-sync-alt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Integrasi Accurate Online</h5>
                                <p class="text-muted small mb-0">
                                    Status sinkronisasi master data barang terakhir: 
                                    <span class="fw-bold text-dark">{{ $lastSync ?? 'Belum pernah disinkronkan' }}</span>
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('items.sync') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary shadow-sm fw-bold px-4 py-2" onclick="return confirm('Mulai sinkronisasi master data barang dari Accurate Online?')">
                                <i class="fas fa-cloud-download-alt me-2"></i>Sinkronkan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-history text-muted me-2"></i>Log Aktivitas Sistem Terkini
                    </h5>
                    <span class="badge bg-primary">Real-time Audit Trail</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light text-uppercase text-muted">
                                <tr>
                                    <th class="ps-4 py-3" style="width: 20%;">Waktu</th>
                                    <th class="py-3" style="width: 25%;">Pengguna</th>
                                    <th class="py-3" style="width: 20%;">Aksi</th>
                                    <th class="pe-4 py-3" style="width: 35%;">Keterangan Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs ?? [] as $log)
                                    <tr>
                                        <td class="ps-4 text-muted small">
                                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }} WIB
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</div>
                                            <span class="text-muted small">{{ $log->user->role->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if(str_contains(strtolower($log->action), 'create') || str_contains(strtolower($log->action), 'sync'))
                                                <span class="badge bg-light text-success border border-success px-2 py-1">
                                                    <i class="fas fa-plus-circle me-1"></i>{{ $log->action }}
                                                </span>
                                            @elseif(str_contains(strtolower($log->action), 'update') || str_contains(strtolower($log->action), 'approve'))
                                                <span class="badge bg-light text-primary border border-primary px-2 py-1">
                                                    <i class="fas fa-edit me-1"></i>{{ $log->action }}
                                                </span>
                                            @elseif(str_contains(strtolower($log->action), 'delete') || str_contains(strtolower($log->action), 'recount'))
                                                <span class="badge bg-light text-danger border border-danger px-2 py-1">
                                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $log->action }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border px-2 py-1">
                                                    {{ $log->action }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-muted small text-wrap">
                                            {{ $log->description }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                            Belum ada log aktivitas terekam.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt text-warning me-2"></i>Pintasan Administratif
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Akses cepat menu manajemen database internal WMS.</p>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary text-start p-3 shadow-sm rounded transition-all">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-plus text-success fa-lg me-3"></i>
                                <div>
                                    <div class="fw-bold text-dark small">Tambah Pengguna Baru</div>
                                    <span class="text-muted extra-small">Daftarkan staf gudang atau manajemen</span>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('racks.index') }}" class="btn btn-outline-secondary text-start p-3 shadow-sm rounded transition-all">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-qrcode text-primary fa-lg me-3"></i>
                                <div>
                                    <div class="fw-bold text-dark small">Cetak & Kelola QR Rak</div>
                                    <span class="text-muted extra-small">Buka penataan koordinat lokasi rak</span>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary text-start p-3 shadow-sm rounded transition-all">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-barcode text-muted fa-lg me-3"></i>
                                <div>
                                    <div class="fw-bold text-dark small">Lihat Pemetaan SKU</div>
                                    <span class="text-muted extra-small">Periksa relasi item terhadap gembok rak</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="alert alert-light border text-muted small p-3 mt-4 mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Keamanan akses menu ini dikelola secara ketat melalui protokol Role-Based Access Control (RBAC) untuk memastikan integritas data operasional.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small {
        font-size: 0.75rem;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    .transition-all:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
    }
</style>
@endsection