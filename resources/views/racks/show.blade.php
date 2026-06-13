@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card text-center p-5 shadow" style="width: 400px; border: 2px dashed #ccc;">
        <h5 class="mb-4 text-uppercase fw-bold text-muted">Label Rak Gudang</h5>
        
        <div class="mb-4 d-flex justify-content-center">
            <div class="bg-white p-2 border rounded">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($rack->qr_code) !!}
            </div>
        </div>
        
        <h1 class="fw-bold text-dark mb-0" style="letter-spacing: 3px;">{{ $rack->code }}</h1>
        <p class="text-muted mt-2 mb-4">PT Sigma Berkat Sejati</p>

        <div class="d-print-none mt-3">
            <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
                <i class="fas fa-print me-2"></i>Cetak Label
            </button>
            <a href="{{ route('racks.index') }}" class="btn btn-outline-secondary ms-2">
                Kembali
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body { 
            background-color: white !important; 
            margin: 0;
            padding: 0;
        }
        nav, header, footer, .sidebar { 
            display: none !important; 
        }
        .card { 
            border: none !important; 
            box-shadow: none !important; 
            margin: 0 auto;
        }
        .container {
            align-items: flex-start !important;
            padding-top: 20px;
        }
    }
</style>
@endsection