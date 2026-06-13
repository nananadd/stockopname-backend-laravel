<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class AccuratePenyesuaianExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell
{
    protected $cycle;

    public function __construct($cycle)
    {
        $this->cycle = $cycle;
    }

    // Menentukan titik kordinat awal penulisan Excel
    public function startCell(): string
    {
        // Perintah Excel untuk mengeksekusi fungsi headings() mulai dari sel A5
        return 'A5';
    }

    public function collection()
    {
        return $this->cycle->details;
    }

    public function headings(): array
    {
        // Karena sistem mulai dari A5
        // Array pertama ini akan menempati Baris ke-5 (sebagai Header Accurate)
        // Array kedua ini akan menempati Baris ke-6 (sebagai jarak/baris kosong)
        return [
            [
                '', '', 'Kode Barang', '', 'Nama Barang', '', '', 'Kuantitas', '', 'Satuan', '', 'Hitung #1', ''
                // , 'Hitung #2', ''
            ],
            [
                '', '', '', '', '', '', '', '', '', '', '', '', ''
                // , '', ''
            ]
        ];
    }

    public function map($detail): array
    {
        // Data ini akan tercetak mulai baris ke 7 ke bawah dengan posisi kolom sesuai Accurate
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
            // '', // M
            // '', // N: Hitung #2
            // ''  // O
        ];
    }
}