@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">
        <i class="fas fa-pallet text-primary me-2"></i>Data Rak
    </h3>
    <a href="{{ route('racks.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> Tambah Rak Baru
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-sigma-black text-white rounded">
        <form action="{{ route('racks.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label text-white-50 small">Pencarian Rak</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Cari Kode Rak atau QR Code..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 mb-1">Cari Rak</button>
                @if(request('search'))
                    <a href="{{ route('racks.index') }}" class="text-white-50 small text-decoration-none d-block text-center mt-2">
                        <i class="fas fa-times-circle me-1"></i>Reset Pencarian
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode Rak</th>
                        <th>Visual QR Code</th>
                        <th>Status Hitung</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($racks as $rack)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $rack->code }}</td>
                        
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-white p-1 border rounded shadow-sm me-3">
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate($rack->qr_code) !!}
                                </div>
                                <span class="text-secondary fw-medium">{{ $rack->qr_code }}</span>
                            </div>
                        </td>
                        <td>
                            @if($rack->is_locked)
                                <span class="badge bg-danger"><i class="fas fa-lock me-1"></i> Sedang Dihitung</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-unlock me-1"></i> Tersedia</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('racks.show', $rack->id) }}" class="btn btn-sm btn-outline-info me-1" title="Lihat & Cetak Label QR">
                                <i class="fas fa-print"></i>
                            </a>

                            <a href="{{ route('racks.edit', $rack->id) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit Data">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('racks.destroy', $rack->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger swal-confirm" 
                                    data-swal-title="Hapus Rak?" 
                                    data-swal-text="Menghapus rak ini akan melepaskan semua barang di dalamnya!" 
                                    data-swal-confirm="Ya, Hapus Rak">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-pallet fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Data rak tidak ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-footer bg-white py-3">
        {{ $racks->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection