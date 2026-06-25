@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark">
                <i class="fas fa-plus-circle text-primary me-2"></i>Edit Barang Baru
            </h3>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('items.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="form-label fw-bold">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $item->sku) }}" placeholder="Contoh: KRT-A4-70G" required>
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" placeholder="Contoh: Kertas HVS A4 70 Gram" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Stok Sistem (System Stock) <span class="text-danger">*</span></label>
                        <input type="number" name="system_stock" class="form-control @error('system_stock') is-invalid @enderror" value="{{ old('system_stock', $item->system_stock) }}" min="0" placeholder="Contoh: 150" required>
                        <div class="form-text text-muted">Jumlah stok awal barang berdasarkan data sistem saat ini.</div>
                        @error('system_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Satuan Barang <span class="text-danger">*</span>
                        </label>

                        <select name="unit" 
                                id="unitSelect"
                                class="form-select @error('unit') is-invalid @enderror"
                                required>

                            <option value="">-- Pilih atau ketik satuan --</option>

                            @php
                                $units = [
                                    'PCS', 'Set', 'Unit', 'Pasang', 'Pack', 'Dus', 'Box',
                                    'Slop', 'Blister', 'Strip', 'Lusin', 'Gross', 'Ball',
                                    'Roll', 'Meter', 'Batang', 'Rim', 'Lembar', 'Botol',
                                    'Tube', 'Kaleng', 'Pouch', 'Galon', 'Tabung', 'Kg', 'Ikat'
                                ];

                                $currentUnit = old('unit', $item->unit);
                            @endphp

                            @foreach($units as $unit)
                                <option value="{{ $unit }}"
                                    {{ $currentUnit == $unit ? 'selected' : '' }}>
                                    {{ $unit }}
                                </option>
                            @endforeach

                            {{-- kalau unit lama custom dan tidak ada di list --}}
                            @if($currentUnit && !in_array($currentUnit, $units))
                                <option value="{{ $currentUnit }}" selected>
                                    {{ $currentUnit }}
                                </option>
                            @endif

                        </select>

                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Alokasi Rak (Opsional)
                        </label>

                        @php
                            $currentRackId = old('rack_id', $item->racks->first()->id ?? '');
                        @endphp

                        <select name="rack_id" 
                                id="rackSelect"
                                class="form-select @error('rack_id') is-invalid @enderror">

                            <option value="">-- Pilih Rak --</option>

                            @foreach($racks as $rack)
                                <option value="{{ $rack->id }}"
                                    {{ $currentRackId == $rack->id ? 'selected' : '' }}>
                                    {{ $rack->code }}
                                </option>
                            @endforeach

                        </select>

                        <div class="form-text text-muted">
                            Anda bisa mengalokasikan rak nanti.
                        </div>

                        @error('rack_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg swal-confirm" 
                            data-swal-title="Perbarui Data Barang?" 
                            data-swal-text="Pastikan nama dan SKU sudah tepat." 
                            data-swal-icon="info">
                            <i class="fas fa-save me-2"></i>Perbarui Data Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple {
    min-height: 38px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.375rem !important;
    padding: 4px 8px !important;
}

.select2-container--default .select2-selection--multiple {
    display: flex !important;
    align-items: center;
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
    $('#unitSelect').select2({
        tags: true,
        placeholder: "Pilih atau ketik satuan",
        allowClear: true
    });

    $('#rackSelect').select2({
        placeholder: "Pilih Rak",
        width: '100%'
    });
});
</script>
@endsection