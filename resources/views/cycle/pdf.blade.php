<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stock Opname</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .data-table th { background-color: #f2f2f2; }
        .text-left { text-align: left !important; }
        .signature { width: 100%; margin-top: 50px; }
        .signature td { text-align: center; width: 50%; padding-top: 70px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>PT. SIGMA BERKAT SEJATI</h2>
        <h3>LAPORAN HASIL STOCK OPNAME (CYCLE COUNT)</h3>
        <hr>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Lokasi Rak</strong></td>
            <td width="35%">: {{ $cycle->rack->code ?? '-' }}</td>
            <td width="20%"><strong>Tanggal Hitung</strong></td>
            <td width="30%">: {{ \Carbon\Carbon::parse($cycle->started_at)->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td>: {{ strtoupper($cycle->status) }}</td>
            <td><strong>ID Laporan</strong></td>
            <td>: #CC-{{ str_pad($cycle->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th class="text-left">Nama Barang</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cycle->details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $detail->item->name ?? '-' }}</td>
                <td>{{ $detail->system_stock_snapshot }}</td>
                <td>{{ $detail->physical_stock }}</td>
                <td>
                    @if($detail->difference > 0)
                        +{{ $detail->difference }}
                    @else
                        {{ $detail->difference }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <br><br><br>
                <p><strong>( ________________________ )</strong></p>
                <p>Kepala Gudang / Supervisor</p>
            </td>
            <td>
                <p>Dihitung Oleh,</p>
                <br><br><br>
                <p><strong>( ________________________ )</strong></p>
                <p>Staf Gudang</p>
            </td>
        </tr>
    </table>

</body>
</html>