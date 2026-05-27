<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ItemsImport implements ToModel, WithStartRow
{
    /**
     * Memulai baca data dari baris ke-6 (mengabaikan header Accurate yang berantakan)
     */
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        // Cegah error jika baris kosong
        if (!isset($row[2]) || $row[2] == null) {
            return null;
        }

        // Mapping Index Array sesuai kolom di file aslimu:
        // $row[2] = Kode Barang
        // $row[4] = Nama Barang
        // $row[7] = Kuantitas
        // $row[9] = Satuan

        return Item::updateOrCreate(
            ['sku' => $row[2]], // Cari berdasarkan SKU / Kode Barang
            [
                'name' => $row[4],
                'system_stock' => (int) $row[7], // Pastikan jadi angka
                'unit' => $row[9],
            ]
        );
    }
}