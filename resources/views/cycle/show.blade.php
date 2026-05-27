@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h3 class="fw-bold text-primary">
            <i class="fas fa-file-invoice me-2"></i>Detail Hitungan: {{ $cycle->rack->code ?? '-' }}
        </h3>
        <p class="text-muted mb-0">PT. SIGMA BERKAT SEJATI — WAREHOUSE MANAGEMENT SYSTEM</p>
    </div>
    <a href="{{ route('cycle.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row mb-5">
    <div class="col-md-8">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>Informasi Sesi</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="25%">Waktu Mulai</td>
                        <td class="fw-bold text-primary">: {{ $cycle->started_at }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>: 
                            @if($cycle->status == 'draft')
                                <span class="badge bg-warning text-dark py-1 px-3">MENUNGGU REVIEW</span>
                            @elseif($cycle->status == 'submitted')
                                <span class="badge bg-info text-dark py-1 px-3">STAFF SUDAH SUBMIT</span>
                            @elseif($cycle->status == 'approved')
                                <span class="badge bg-success py-1 px-3">DISETUJUI</span>
                            @elseif($cycle->status == 'reviewed')
                                <span class="badge bg-info text-dark py-1 px-3">DIREVIEW</span>
                            @else
                                <span class="badge bg-secondary py-1 px-3">{{ strtoupper($cycle->status) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-box me-2"></i>Hasil Perbandingan Stok</h5>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <form action="{{ route('cycle.sync', $cycle->id) }}" method="POST" onsubmit="return confirm('Sistem akan mengecek apakah ada barang baru di rak ini. Lanjutkan?');">
                    @csrf
                    @if(in_array($cycle->status, ['draft', 'submitted', 'reviewed', 'approved','recount']))
                        <button type="submit" class="btn btn-warning shadow-sm fw-bold">
                            <i class="fas fa-sync-alt me-2"></i> Sinkronkan Barang Baru
                        </button>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped align-middle mb-0">
                        <thead class="table-dark text-center" style="background-color: var(--sigma-blue-dark) !important;">
                            <tr>
                                <th class="text-start ps-4">Nama Barang</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik (HP)</th>
                                <th>Selisih (Variance)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cycle->details as $detail)
                                @php
                                    // LOGIKA SELISIH (difference) dari DB
                                    $selisih = $detail->difference; 
                                    
                                    $badgeClass = 'badge-sigma-success';
                                    $selisihText = 'Sesuai (0)';
                                    
                                    if ($selisih < 0) {
                                        $badgeClass = 'badge-sigma-danger';
                                        $selisihText = 'Kurang ' . abs($selisih);
                                    } elseif ($selisih > 0) {
                                        $badgeClass = 'badge-sigma-info text-dark';
                                        $selisihText = 'Lebih ' . $selisih;
                                    }
                                @endphp
                                
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $detail->item->name ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ $detail->system_stock_snapshot }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $detail->physical_stock }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $selisihText }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($cycle->details->count() > 0)
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <th colspan="3">Total Selisih Rak</th>
                                <th class="text-center fs-5 text-primary">{{ $cycle->details->sum('difference') }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5>Otorisasi & Aksi</h5>
                            <div class="d-flex flex-column gap-3 mt-3">

                                @if(auth()->user()->role_id == 4)
                                    @if($cycle->status != 'approved')
                                        <form action="{{ route('cycle.recount', $cycle->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label>Catatan untuk Staf (Opsional):</label>
                                                <textarea name="notes" class="form-control" placeholder="Contoh: Tolong cek ulang laci nomor 3..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-arrow-repeat"></i> Minta Hitung Ulang
                                            </button>
                                        </form>

                                        <hr class="text-muted"> 

                                        <div>
                                            <form action="{{ route('cycle.approve', $cycle->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                                                    <i class="fas fa-check-circle me-2"></i> Setujui Hasil Hitungan
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-lock me-2"></i> Laporan Telah Disetujui (Menunggu Review Manajer)
                                            </button>
                                        </div>
                                    @endif
                                @endif


                                @if(auth()->user()->role_id == 3)
                                    @if($cycle->status == 'approved' && is_null($cycle->reviewed_by))
                                        <div class="alert alert-warning mb-2">
                                            <i class="fas fa-exclamation-triangle me-2"></i> Laporan ini telah disetujui Supervisor. Silakan lakukan validasi akhir.
                                        </div>
                                        <form action="{{ route('cycle.review', $cycle->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm">
                                                <i class="fas fa-user-check me-2"></i> Tandai Telah Direview
                                            </button>
                                        </form>
                                    
                                    @elseif(!is_null($cycle->reviewed_by))
                                        <div>
                                            <button class="btn btn-secondary mb-3" disabled>
                                                <i class="fas fa-check-double me-2"></i> Laporan Telah Direview
                                            </button>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-dark dropdown-toggle px-4 py-2" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-download me-2"></i> Export Data Penyesuaian
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('cycle.pdf', $cycle->id) }}">Download Laporan PDF</a></li>
                                                <li><a class="dropdown-item" href="{{ route('cycle.export.excel', $cycle->id) }}">Download Laporan Excel</a></li>
                                                <li><a class="dropdown-item" href="{{ route('cycle.export.accurate', $cycle->id) }}">Download Excel Accurate Online</a></li>
                                            </ul>
                                        </div>
                                    
                                    @else
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i> Laporan masih dalam proses perhitungan atau menunggu persetujuan Supervisor.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <footer class="text-center text-muted pb-5 pt-3 border-top mt-5">
    <p class="mb-0">© {{ date('Y') }} PT. SIGMA BERKAT SEJATI — WAREHOUSE MANAGEMENT SYSTEM</p>
    <p class="small text-muted mb-0">Aplikasi Stock Opname Terintegrasi</p>
</footer> -->
@endsection