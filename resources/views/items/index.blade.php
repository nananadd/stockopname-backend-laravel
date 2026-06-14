@extends('layouts.app')

@section('content')

<style>
.select2-container .select2-selection--single {
    height: 38px !important;
    border: none !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}
</style>


<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    
    <h3 class="fw-bold text-dark mb-0">
        <i class="fas fa-box-open text-primary me-2"></i>
        Data Barang
    </h3>

    <div class="d-flex align-items-center gap-2 flex-wrap">

        <a href="{{ route('items.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i>
            Tambah Barang Baru
        </a>

        <form action="{{ route('items.import') }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="d-flex align-items-center gap-2">
            @csrf

            <input 
                type="file" 
                name="file_accurate"
                id="excelFile"
                class="d-none"
                accept=".xlsx,.xls,.csv"
                required
                onchange="updateFileName(this)"
            >

            <button type="button" 
                    class="btn btn-outline-success shadow-sm"
                    data-bs-toggle="modal" 
                    data-bs-target="#importExcelModal">
                <i class="fas fa-paperclip me-1"></i>
                Attach Excel
            </button>
        </form>
    </div>
</div>


<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-sigma-black text-white rounded">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-white-50 small">Pencarian Barang</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text"
                            id="searchInput"
                            class="form-control border-0"
                            placeholder="Cari nama barang atau SKU..."
                            value="{{ request('search') }}"
                            oninput="debounceSearch()">
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white-50 small">Filter Lokasi Rak</label>
                <select id="rackFilter" 
                        name="rack_id" 
                        class="form-select border-0 select2-filter"
                        onchange="executeSearch()">
                    <option value="">-- Semua Rak --</option>
                    @foreach($racks as $rack)
                        <option value="{{ $rack->id }}" {{ request('rack_id') == $rack->id ? 'selected' : '' }}>
                            {{ $rack->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-white-50 small">
                    Filter Satuan
                </label>

                    <select id="unitFilter" 
                            class="form-select border-0 select2-filter" 
                            onchange="executeSearch()">
                    <option value="">-- Semua Satuan --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit }}"
                            {{ request('unit') == $unit ? 'selected' : '' }}>
                            {{ $unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-1 text-center">
                <a href="{{ route('items.index') }}"
                class="btn btn-outline-light w-100"
                title="Reset Filter">

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
                        <th class="ps-4">SKU</th>
                        <th>Nama Barang</th>
                        <th>Lokasi Rak</th>
                        <th>Stok Sistem</th>
                        <th>Satuan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="item-table-body">
                    @forelse($items as $item)
                    <tr>
                        <td class="ps-4 fw-bold text-sigma-black">{{ $item->sku }}</td>
                        <td class="text-dark">{{ $item->name }}</td>
                        <td>
                            @if($item->racks->isNotEmpty())
                                <span class="badge bg-sigma-black">
                                    {{ $item->racks->first()->code }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    Belum Dialokasikan
                                </span>
                            @endif
                        </td>
                        <td class="text-dark">{{ $item->system_stock }}</td>
                        <td class="text-dark">{{ $item->unit }}</td>

                        <td class="text-center">
                            <a href="{{ route('items.edit', $item->id) }}" 
                               class="btn btn-sm btn-outline-secondary me-1">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('items.destroy', $item->id) }}" 
                                  method="POST" 
                                  class="d-inline">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger swal-confirm" 
                                    data-swal-title="Hapus Barang?" 
                                    data-swal-text="Data barang (SKU) ini akan dihapus permanen dari sistem.">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-box fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Data barang tidak ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>
    
    <div id="pagination-container" class="card-footer bg-white py-3">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
</div>

<div class="modal fade" id="importExcelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-excel text-success me-2"></i>
                    Import Data Accurate
                </h5>

                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal">
                </button>
            </div>

            <form action="{{ route('items.import') }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf

                <div class="modal-body">

                    <label class="form-label fw-semibold">
                        Upload File Excel
                    </label>

                    <input type="file" 
                           name="file_accurate" 
                           class="form-control"
                           accept=".xlsx,.xls,.csv"
                           required>

                    <small class="text-muted">
                        Format yang didukung: .xlsx, .xls, .csv
                    </small>

                </div>

                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" 
                            class="btn btn-success">
                        <i class="fas fa-file-import me-1"></i>
                        Import
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function updateFileName(input) {
        let fileName = "Belum ada file";

        if(input.files.length > 0){
            fileName = input.files[0].name;
        }

        document.getElementById("selectedFileName").innerText = fileName;
    }
</script>

<script>
let typingTimer;

function debounceSearch() {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(executeSearch, 300);
}

function executeSearch() {
    const searchVal = document.getElementById('searchInput').value;
    const rackVal = document.getElementById('rackFilter').value;
    const unitVal = document.getElementById('unitFilter').value;
    const params = new URLSearchParams();

    if (searchVal)
        params.append('search', searchVal);
    if (rackVal)
        params.append('rack_id', rackVal);
    if (unitVal)
        params.append('unit', unitVal);
    
    const url =`{{ route('items.index') }}?${params.toString()}`;

    loadTableData(url);
}

function loadTableData(url) {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newBody =doc.getElementById('item-table-body');
        const currentBody = document.getElementById('item-table-body');

        if(newBody && currentBody){
            currentBody.innerHTML =
                newBody.innerHTML;
        }

        const newPagination = doc.getElementById('pagination-container');
        const currentPagination = document.getElementById('pagination-container');

        if(newPagination && currentPagination){
            currentPagination.innerHTML =
                newPagination.innerHTML;
        }

        window.history.pushState({}, '', url);
    });
}

document.addEventListener('click', function(e){
    const paginationLink = e.target.closest('.pagination a');

    if(paginationLink){
        e.preventDefault();
        loadTableData(
            paginationLink.href
        );
    }

});

$(document).ready(function () {

    $('.select2-filter').select2({
        width: '100%',
        placeholder: 'Pilih...',
        allowClear: true
    });

});

function initSelect2() {

    $('.select2-filter').select2({
        width: '100%',
        placeholder: 'Pilih...',
        allowClear: true
    });

}

document.addEventListener("DOMContentLoaded", function () {
    initSelect2();
});
</script>

@endsection