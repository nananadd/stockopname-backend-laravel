<!-- @extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark">
                <i class="fas fa-barcode text-primary me-2"></i>Mulai Cycle Count
            </h3>
            <a href="{{ route('cycle.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-clipboard-check fa-4x text-sigma-magenta opacity-75 mb-3"></i>
                    <h5 class="fw-bold">Pilih Lokasi Rak</h5>
                    <p class="text-muted small">Pilih rak yang ingin dihitung stoknya. Rak yang sedang dikunci tidak akan muncul di daftar ini.</p>
                </div>

                <form action="{{ route('cycle.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold">Lokasi Rak Tersedia <span class="text-danger">*</span></label>
                        <select name="rack_id" class="form-select form-select-lg shadow-none @error('rack_id') is-invalid @enderror" required>
                            <option value="">-- Silakan Pilih Rak --</option>
                            @foreach($racks as $rack)
                                <option value="{{ $rack->id }}">
                                    {{ $rack->code }} ({{ $rack->qr_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('rack_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold" onclick="return confirm('Mulai hitung rak ini? Rak akan otomatis dikunci dari transaksi.')">
                            <i class="fas fa-play me-2"></i> Mulai Penghitungan Fisik
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection -->