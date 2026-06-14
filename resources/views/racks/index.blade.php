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
            <div class="row g-3 align-items-end">
                
                <div class="col-md-5">
                    <label class="form-label text-white-50 small">Pencarian Rak</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-0" 
                            placeholder="Cari Kode atau QR Code..." 
                            value="{{ request('search') }}" autocomplete="off" oninput="debounceSearch()">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label text-white-50 small">Kategori</label>
                    <select id="categoryFilter" class="form-select border-0" onchange="executeSearch()">
                        <option value="">Semua Kategori</option>
                        <option value="A" {{ request('category') == 'A' ? 'selected' : '' }}>Kategori A</option>
                        <option value="B" {{ request('category') == 'B' ? 'selected' : '' }}>Kategori B</option>
                        <option value="C" {{ request('category') == 'C' ? 'selected' : '' }}>Kategori C</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white-50 small">Status Rak</label>
                    <select id="statusFilter" class="form-select border-0" onchange="executeSearch()">
                        <option value="">Semua Status</option>
                        <option value="0" {{ request('is_locked') == '0' ? 'selected' : '' }}>Aktif (Terbuka)</option>
                        <option value="1" {{ request('is_locked') == '1' ? 'selected' : '' }}>Terkunci</option>
                    </select>
                </div>
                
                <div class="col-md-1 text-center">
                    <a href="{{ route('racks.index') }}" class="btn btn-outline-light w-100" title="Reset Filter">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
                
            </div>
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
                            <th>Kategori</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rack-table-body">
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
                            <td>
                                <span class="badge bg-black">
                                    {{ $rack->category }}
                                </span>
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
    
        <div id="pagination-container" class="card-footer bg-white py-3">
            {{ $racks->links('pagination::bootstrap-5') }}
        </div>
    </div>

<script>
    let typingTimer;

    function debounceSearch() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(executeSearch, 300);
    }

    // Fungsi Pencarian
    function executeSearch() {
        // Ambil nilai terbaru
        const searchVal = document.getElementById('searchInput').value;
        const catVal = document.getElementById('categoryFilter').value;
        const lockVal = document.getElementById('statusFilter').value;

        // Rangkai URL beserta filternya
        const params = new URLSearchParams();
        if (searchVal) params.append('search', searchVal);
        if (catVal) params.append('category', catVal);
        if (lockVal !== '') params.append('is_locked', lockVal); 

        // Buat URL tujuan ke route index Laravel
        const url = `{{ route('racks.index') }}?${params.toString()}`;

        // Panggil AJAX
        loadTableData(url);
    }

    // Fungsi AJAX untuk mengambil HTML dari Laravel
    function loadTableData(url) {
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newBody = doc.getElementById('rack-table-body');
            const currentBody = document.getElementById('rack-table-body');

            if (newBody && currentBody) {
                currentBody.innerHTML = newBody.innerHTML;
            }

            const newPagination = doc.getElementById('pagination-container');
            const currentPagination = document.getElementById('pagination-container');

            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }

            window.history.pushState({}, '', url);
        });
    }

    //Agar tombol Pagination tidak refresh halaman
    document.addEventListener('click', function(e) {
        // Mencari apakah yang diklik adalah tombol link pagination bawaan Laravel
        const paginationLink = e.target.closest('.pagination a');
        
        if (paginationLink) {
            e.preventDefault(); // Cegah reload halaman
            const url = paginationLink.href;
            loadTableData(url); // Tarik data halamannya pakai AJAX
        }
    });

    // Posisikan kursor selalu di akhir teks pada kotak pencarian saat halaman baru diload
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput && searchInput.value.length > 0) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
    });
</script>
@endsection