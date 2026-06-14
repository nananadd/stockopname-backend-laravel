<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ItemsImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
{
    /**
     * Memulai baca data dari baris ke-6
     */
    public function startRow(): int
    {
        return 6;
    }

    /**
     * filtering 
     */
    public function model(array $row)
    {
        // $row[2] = Kolom C (Kode Barang)
        // $row[4] = Kolom E (Nama Barang)
        // $row[7] = Kolom H (Kuantitas)
        // $row[9] = Kolom J (Satuan)

        $sku = isset($row[2]) ? trim($row[2]) : null;
        $name = isset($row[4]) ? trim($row[4]) : null;


        // Filter lewati baris jika SKU kosong / null
        if (empty($sku)) {
            return null; 
        }

        // Filter lewati baris jika itu header
        if ($sku === 'Kode Barang' || $sku === 'Kode' || $name === 'Nama Barang') {
            return null;
        }

        // Filter lewati baris footer atau subtotal
        if (str_contains(strtolower($sku), 'total') || str_contains(strtolower($sku), 'page')) {
            return null;
        }
        
        // clean dari koma/titik jika ada
        $qtyRaw = isset($row[7]) ? $row[7] : 0;
        $qtyClean = (int) preg_replace('/[^0-9]/', '', $qtyRaw); 

        // Update atau Create Item
        return new Item([
            'sku'          => $sku,
            'name'         => $name,
            'system_stock' => $qtyClean,
            'unit'         => isset($row[9]) ? trim($row[9]) : 'PCS',
        ]);
    }

    /**
     * optimasi
     */
    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}