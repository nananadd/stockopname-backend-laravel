<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rack;
use App\Models\Item;
use App\Models\CycleCount;
use App\Models\CycleCountDetail;

class SyncController extends Controller
{
    // ==========================================
    // API PULL: Mengirim data ke Mobile (Termasuk Jadwal)
    // ==========================================
    public function pullMasterData()
    {
        $racks = Rack::with('items')->get(); 
        $items = Item::all();
        
        // Tarik tugas jadwal khusus untuk Staf yang sedang login
        $myTasks = CycleCount::with('rack')
            ->where('counted_by', auth()->id())
            ->whereIn('status', ['draft','recount']) // Hanya tarik yang belum dikerjakan
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return response()->json([
            'racks' => $racks,
            'items' => $items,
            'my_tasks' => $myTasks, // Dikirim ke Flutter
        ], 200);
    }

    // ==========================================
    // API PUSH: Menerima hasil hitung dari Mobile
    // ==========================================
    public function pushCycleCount(Request $request)
    {
        $data = $request->validate([
            'cycle_counts' => 'required|array',
            'cycle_counts.*.rack_id' => 'required|exists:racks,id',
            'cycle_counts.*.started_at' => 'required|date',
            'cycle_counts.*.finished_at' => 'required|date',
            'cycle_counts.*.details' => 'required|array',
        ]);

        foreach ($data['cycle_counts'] as $cycleData) {
            
            // 1. CARI DOKUMEN LAMA (Draft ATAU Recount)
            $cycle = CycleCount::where('rack_id', $cycleData['rack_id'])
                               ->where('counted_by', auth()->id())
                               // INI KUNCI UTAMANYA: Pakai whereIn, bukan where biasa!
                               ->whereIn('status', ['draft', 'recount']) 
                               ->first();

            if ($cycle) {
                // JIKA KETEMU: Timpa dokumen yang lama, ubah statusnya jadi submitted lagi
                $cycle->update([
                    'status' => 'submitted',
                    'started_at' => $cycleData['started_at'],
                    'finished_at' => $cycleData['finished_at'],
                ]);
            } else {
                // JIKA TIDAK KETEMU: Buat laporan baru (Hitung Dadakan)
                $cycle = CycleCount::create([
                    'rack_id' => $cycleData['rack_id'],
                    'status' => 'submitted', 
                    'started_at' => $cycleData['started_at'],
                    'finished_at' => $cycleData['finished_at'],
                    'counted_by' => auth()->id(), 
                ]);
            }

            // 2. TIMPA DETAIL BARANGNYA (Jangan sampai double juga)
            foreach ($cycleData['details'] as $detail) {
                $item = Item::find($detail['item_id']);
                
                // updateOrCreate akan menimpa (menumpuk) hasil stok fisik lama
                // jika ID Laporan dan ID Barangnya cocok.
                CycleCountDetail::updateOrCreate(
                    [
                        'cycle_count_id' => $cycle->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'system_stock_snapshot' => $item->system_stock, 
                        'physical_stock' => $detail['physical_stock'],
                        'difference' => $detail['physical_stock'] - $item->system_stock,
                    ]
                );
            }
        }

        return response()->json(['message' => 'Sync successful'], 200);
    }
}