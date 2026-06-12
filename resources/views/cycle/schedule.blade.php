@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark">
                <i class="fas fa-calendar-plus text-primary me-2"></i>Buat Jadwal Stock Opname
            </h3>
            <a href="{{ route('cycle.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5">
                <form action="{{ route('cycle.storeSchedule') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lokasi Rak</label>
                            <select name="rack_id" id="rackSelect"class="form-select form-select-lg @error('rack_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Rak --</option>
                                @foreach($racks as $rack)
                                    <option value="{{ $rack->id }}">{{ $rack->code }} ({{ $rack->qr_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Petugas (Staff)</label>
                            <select name="counted_by" id="staffSelect"class="form-select form-select-lg @error('counted_by') is-invalid @enderror" required>
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Rencana Tanggal Hitung</label>
                            <input type="date" name="scheduled_at" class="form-control form-control-lg @error('scheduled_at') is-invalid @enderror" value="{{ date('Y-m-d') }}" required>
                            <small class="text-muted">Notifikasi akan dikirimkan ke HP petugas pada tanggal tersebut.</small>
                        </div>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 shadow swal-confirm" 
                            data-swal-title="Kirim Jadwal?" 
                            data-swal-text="Notifikasi penugasan akan dikirimkan ke HP staf." 
                            data-swal-icon="info">
                            <i class="fas fa-paper-plane me-2"></i> Simpan & Kirim Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.select2-container .select2-selection--single {
    min-height: 48px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.5rem !important;
    padding: 8px 12px !important;
}

.select2-container {
    width: 100% !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 10px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    display: flex !important;
    align-items: center !important;
    height: 100% !important;
    line-height: normal !important;
}
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    $('#rackSelect').select2({
        placeholder: "Pilih Rak",
        width: '100%'
    });

    $('#staffSelect').select2({
        placeholder: "Pilih Petugas",
        width: '100%'
    });

});
</script>
@endsection