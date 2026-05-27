<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell; // 1. Import fitur penentu sel

class AccuratePenyesuaianExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell // 2. Tambahkan implements-nya
{
    protected $cycle;

    public function __construct($cycle)
    {
        $this->cycle = $cycle;
    }

    // 3. FUNGSI AJAIB: Menentukan titik kordinat awal penulisan Excel
    public function startCell(): string
    {
        // Kita perintahkan Excel untuk mengeksekusi fungsi headings() mulai dari sel A5
        return 'A5';
    }

    public function collection()
    {
        return $this->cycle->details;
    }

    public function headings(): array
    {
        // Karena sistem mulai dari A5, maka:
        // Array pertama ini akan menempati Baris ke-5 (sebagai Header Accurate)
        // Array kedua ini akan menempati Baris ke-6 (sebagai jarak/baris kosong)
        // Sehingga, data mapping otomatis akan tertulis mulai di BARIS KE-7!
        return [
            [
                '', '', 'Kode Barang', '', 'Nama Barang', '', '', 'Kuantitas', '', 'Satuan', '', 'Hitung #1', '', 'Hitung #2', ''
            ],
            [
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ]
        ];
    }

    public function map($detail): array
    {
        // Data di bawah ini akan tercetak mulai baris ke-7 ke bawah dengan posisi kolom yang melompat-lompat sesuai Accurate
        return [
            '', // A
            '', // B
            $detail->item->sku ?? '-', // C: Kode Barang
            '', // D
            $detail->item->name ?? '-', // E: Nama Barang
            '', // F
            '', // G
            $detail->system_stock_snapshot, // H: Kuantitas Sistem
            '', // I
            $detail->item->unit ?? '-', // J: Satuan
            '', // K
            $detail->physical_stock, // L: Hitung #1 (Angka dari WMS masuk ke sini)
            '', // M
            '', // N: Hitung #2
            ''  // O
        ];
    }
}