<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rack;
use App\Models\CycleCount;
use App\Models\CycleCountDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDailyCycleCount extends Command
{
    protected $signature = 'cyclecount:generate';
    protected $description = 'Otomatis membuat jadwal Cycle Count harian berdasarkan kategori ABC';

    public function handle()
    {
        $staffs = User::where('role_id', 5)
                      ->where('is_present', 1)
                      ->get();
        
        if ($staffs->isEmpty()) {
            $this->error('Tidak ada staf yang aktif!');
            return;
        }

        $staffCount = $staffs->count();
        $staffIndex = 0; 
        $tasksCreated = 0;

        $racksPerStaff = 2; 
        $dailyQuota = $staffCount * $racksPerStaff; 

        // filter rak yang TIDAK TERKUNCI agar lebih efisien
        $racks = Rack::where('is_locked', 0)
                     ->orderByRaw('ISNULL(last_counted_at) DESC')
                     ->orderBy('last_counted_at', 'asc')
                     ->get();

        foreach ($racks as $rack) {
            if ($tasksCreated >= $dailyQuota) {
                break; 
            }

            $needsCounting = false;
            
            if (is_null($rack->last_counted_at)) {
                $needsCounting = true;
            } else {
                $daysSinceLastCount = Carbon::parse($rack->last_counted_at)->diffInDays(now());

                if ($rack->category == 'A' && $daysSinceLastCount >= 30) $needsCounting = true;
                if ($rack->category == 'B' && $daysSinceLastCount >= 90) $needsCounting = true;
                if ($rack->category == 'C' && $daysSinceLastCount >= 180) $needsCounting = true;
            }

            if ($needsCounting) {
                $existingDraft = CycleCount::where('rack_id', $rack->id)
                                           ->whereIn('status', ['draft', 'recount'])
                                           ->exists();

                if (!$existingDraft) {
                    // Gunakan DB Transaction agar pembuatan jadwal dan snapshot tidak terputus
                    DB::transaction(function () use ($rack, $staffs, &$staffIndex, &$tasksCreated, $staffCount) {
                        
                        // BUAT HEADER JADWAL
                        $cycleCount = CycleCount::create([
                            'rack_id' => $rack->id,
                            'status' => 'draft',
                            'counted_by' => $staffs[$staffIndex]->id, 
                            'scheduled_at' => now(),
                        ]);

                        // Ambil semua barang yang SAH/SUDAH DIALOKASIKAN ke rak ini
                        $itemsInRack = DB::table('item_rack')
                                         ->where('rack_id', $rack->id)
                                         ->where('stock_at_location', '>', 0)
                                         ->get();

                        foreach ($itemsInRack as $item) {
                            // Simpan ke tabel detail opname sebagai 'System Stock' 
                            CycleCountDetail::create([
                                'cycle_count_id'        => $cycleCount->id,
                                'item_id'               => $item->item_id,
                                'system_stock_snapshot' => $item->stock_at_location,
                                'physical_stock'        => 0,
                                'difference'            => 0 - $item->stock_at_location,
                            ]);
                        }

                        // KUNCI RAK
                        $rack->update(['is_locked' => 1]);

                        $tasksCreated++;
                        $staffIndex = ($staffIndex + 1) % $staffCount; 
                    });
                }
            }
        }

        $this->info("Berhasil membuat $tasksCreated jadwal Cycle Count harian baru beserta data Snapshot!");
    }
}