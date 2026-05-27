@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark">
                <i class="fas fa-edit text-primary me-2"></i>Edit Data Rak: {{ $rack->code }}
            </h3>
            <a href="{{ route('racks.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('racks.update', $rack->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Lokasi Gudang <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouseSelect"class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Gudang Lokasi Rak --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                    {{ old('warehouse_id', $rack->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Kode Rak <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code', $rack->code) }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-bold">Kategori Rak (Frekuensi Hitung)<span class="text-danger">*</span></label>
                        <select name="category" id="categorySelect" class="form-select" required>
                            <option value="A" {{ (old('category', $rack->category) == 'A') ? 'selected' : '' }}>
                                Kategori A (Fast Moving - 30 Hari)
                            </option>
                            <option value="B" {{ (old('category', $rack->category) == 'B') ? 'selected' : '' }}>
                                Kategori B (Medium Moving - 90 Hari)
                            </option>
                            <option value="C" {{ (old('category', $rack->category) == 'C') ? 'selected' : '' }}>
                                Kategori C (Slow Moving - 180 Hari)
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">QR Code Identitas <span class="text-danger">*</span></label>
                        <input type="text" name="qr_code" class="form-control @error('qr_code') is-invalid @enderror" 
                               value="{{ old('qr_code', $rack->qr_code) }}" required>
                        @error('qr_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-bold">Status Keamanan Rak</label>
                        <select name="is_locked" id="lockSelect" class="form-select">
                            <option value="0" {{ old('is_locked', $rack->is_locked) == 0 ? 'selected' : '' }}>Tersedia (Bisa Dihitung)</option>
                            <option value="1" {{ old('is_locked', $rack->is_locked) == 1 ? 'selected' : '' }}>Terkunci (Sedang Proses Opname)</option>
                        </select>
                        <div class="form-text">Gunakan opsi ini hanya jika ingin membuka/mengunci rak secara manual tanpa proses sinkronisasi.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Perbarui Data Rak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.select2-container .select2-selection--single {
    min-height: 38px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.375rem !important;
    padding: 4px 8px !important;
}

.select2-container {
    width: 100% !important;
}
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    $('#warehouseSelect').select2({
        placeholder: "Pilih Gudang Lokasi Rak",
        width: '100%'
    });

    $('#categorySelect').select2({
        placeholder: "Pilih Kategori",
        width: '100%'
    });

    $('#lockSelect').select2({
        placeholder: "Pilih Status",
        width: '100%'
    });

});
</script>

@endsection