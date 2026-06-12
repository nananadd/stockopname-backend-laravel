@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">
        <i class="fas fa-clipboard-list text-primary me-2"></i>Daftar Laporan Cycle Count
    </h3>
    
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('cycle.createSchedule') }}" class="btn btn-primary shadow-sm fw-bold">
            <i class="fas fa-calendar-plus me-1"></i> Buat Jadwal Staf
        </a>

        <form action="{{ route('cycle.generate-auto') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-primary swal-confirm" 
                data-swal-title="Buat Jadwal Otomatis?" 
                data-swal-text="Sistem akan otomatis mengatur jadwal hitung hari ini." 
                data-swal-icon="info">
                <i class="bi bi-robot"></i> Buat Jadwal Otomatis
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Lokasi Rak</th>
                        <th>Waktu Mulai</th>
                        <th>Petugas</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cycles as $cycle)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">#CC-{{ str_pad($cycle->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="fw-bold text-dark">
                            <i class="fas fa-pallet text-secondary me-1"></i> 
                            {{ $cycle->rack->code ?? 'Rak Tidak Diketahui' }}
                        </td>
                        <td>
                            <div class="text-dark">{{ \Carbon\Carbon::parse($cycle->started_at)->format('d M Y') }}</div>
                            <div class="text-muted small"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($cycle->started_at)->format('H:i') }} WIB</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-medium">
                                    {{ $cycle->counter->name ?? 'Belum Ditugaskan' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($cycle->scheduled_at)
                                <span class="text-dark fw-bold">
                                    <i class="far fa-calendar-alt me-1 text-primary"></i>
                                    {{ \Carbon\Carbon::parse($cycle->scheduled_at)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted small">Tanpa Jadwal</span>
                            @endif
                        </td>
                        <td>
                            @if($cycle->status == 'draft')
                                <span class="badge bg-warning text-dark"><i class="fas fa-pen me-1"></i> Proses Hitung</span>
                            @elseif($cycle->status == 'submitted')
                                <span class="badge bg-info text-dark"><i class="fas fa-search me-1"></i> Staff Sudah Submit</span>
                            @elseif($cycle->status == 'reviewed')
                                <span class="badge bg-info text-dark"><i class="fas fa-search me-1"></i> Direview</span>
                            @elseif($cycle->status == 'approved')
                                <span class="badge bg-success"><i class="fas fa-check-double me-1"></i> Disetujui</span>
                            @else
                                <span class="badge bg-secondary">{{ strtoupper($cycle->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('cycle.show', $cycle->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-search me-1"></i> Detail
                            </a>
                            @if($cycle->status != 'approved' && $cycle->status != 'reviewed')
                            <form action="{{ route('cycle.destroy', $cycle->id) }}" method="POST" 
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger swal-confirm" 
                                    data-swal-title="Hapus Jadwal?" 
                                    data-swal-text="Status kunci (gembok) rak akan otomatis terbuka kembali.">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-check fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Belum ada aktivitas hitung stok fisik.</p>
                            <a href="{{ route('cycle.createSchedule') }}" class="btn btn-sm btn-primary mt-3">Mulai Hitung Sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($cycles instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="card-footer bg-white py-3">
        {{ $cycles->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection