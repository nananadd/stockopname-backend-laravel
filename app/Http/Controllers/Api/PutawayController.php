<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Rack;
use DB;

class PutawayController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'code'           => 'required|string', 
            'sku'            => 'required|string',
            'physical_stock' => 'required|integer|min:1'
        ]);

        $scannedInput = trim($request->code);
        $parts = explode('-', $scannedInput);

        if (count($parts) >= 3) {
            $extractedCode = $parts[1] . '-' . $parts[2];
        } else {
            $extractedCode = $scannedInput; 
        }

        $rack = Rack::where('code', $extractedCode)->first();
        $item = Item::where('sku', trim($request->sku))->first();

        // Validasi eksistensi dan kunci rak
        if (!$rack) {
            return response()->json(['success' => false, 'message' => 'Rak tidak ditemukan: ' . $extractedCode], 404);
        }
        if ($rack->is_locked) { 
            return response()->json(['success' => false, 'message' => 'Gagal: Rak sedang dikunci karena dalam proses Stock Opname.'], 403);
        }
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'SKU tidak ditemukan: ' . $request->sku], 404);
        }

        // Cek apakah barang ini sebelumnya sudah ada di rak tersebut
        $existingPivot = DB::table('item_rack')
            ->where('rack_id', $rack->id)
            ->where('item_id', $item->id)
            ->first();

        // Jika ada, tambahkan stok lama dengan stok baru. Jika belum, mulai dari 0.
        $currentStockAtLocation = $existingPivot ? $existingPivot->stock_at_location : 0;
        $newStockAtLocation = $currentStockAtLocation + $request->physical_stock;

        // Simpan / Update ke tabel lokasi
        DB::table('item_rack')->updateOrInsert(
            ['rack_id' => $rack->id, 'item_id' => $item->id],
            ['stock_at_location' => $newStockAtLocation, 'updated_at' => now()]
        );

        // Hitung ulang total stok fisik barang ini di SELURUH RAK
        $totalRealStock = DB::table('item_rack')
                            ->where('item_id', $item->id)
                            ->sum('stock_at_location');
        
        // Update kolom system_stock di tabel master items
        $item->update(['system_stock' => $totalRealStock]);

        return response()->json([
            'success' => true,
            'message' => 'Alokasi barang berhasil disimpan dan master stok diperbarui.'
        ], 200);
    }
}