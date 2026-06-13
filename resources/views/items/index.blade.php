@extends('layouts.app')

@section('content')
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
        <form action="{{ route('items.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-white-50 small">Pencarian Barang</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           class="form-control border-0" 
                           placeholder="Cari nama barang atau SKU..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label text-white-50 small">Filter Lokasi Rak</label>
                <select name="rack_id" class="form-select border-0">
                    <option value="">-- Semua Rak --</option>
                    @foreach($racks as $rack)
                        <option value="{{ $rack->id }}" {{ request('rack_id') == $rack->id ? 'selected' : '' }}>
                            {{ $rack->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 mb-1">
                    Cari & Filter
                </button>

                @if(request('search') || request('rack_id'))
                    <a href="{{ route('items.index') }}" 
                       class="text-white-50 small text-decoration-none d-block text-center mt-2">
                        <i class="fas fa-times-circle me-1"></i>
                        Reset Filter
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
                        <th class="ps-4">SKU</th>
                        <th>Nama Barang</th>
                        <th>Lokasi Rak</th>
                        <th>Stok Sistem</th>
                        <th>Satuan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
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
    
    <div class="card-footer bg-white py-3">
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


<script>
    function updateFileName(input) {
        let fileName = "Belum ada file";

        if(input.files.length > 0){
            fileName = input.files[0].name;
        }

        document.getElementById("selectedFileName").innerText = fileName;
    }
</script>

@endsection