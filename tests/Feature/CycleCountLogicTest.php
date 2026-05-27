<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Rack;
use App\Models\CycleCount;
use App\Models\CycleCountDetail;
use App\Models\Warehouse;

class CycleCountLogicTest extends TestCase
{
    use DatabaseTransactions; 

    public function test_perhitungan_selisih_stok_dan_snapshot_berjalan_benar()
    {
        // 1. ARRANGE
        $user = User::factory()->create(['role_id' => 4]);  // soalnya kalau staff (user 5) harus pakai HP
        
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-TEST-A'], // Gunakan kode unik
            ['name' => 'Gudang Utama Uji Coba']
        );
        
        $item = Item::create([
            'sku' => 'TEST-001',
            'name' => 'Barang Uji Coba',
            'system_stock' => 100, 
            'unit' => 'PCS'
        ]);

        $rack = Rack::create([
            'warehouse_id' => $warehouse->id, 
            'code' => 'RAK-TEST-01',
            'qr_code' => 'QR-RAK-TEST-01', 
            'category' => 'A', 
            'is_locked' => 1
        ]);

        $cycle = CycleCount::create([
            'rack_id' => $rack->id,
            'status' => 'draft',
            'counted_by' => $user->id,
            'scheduled_at' => now(),
            'started_at' => now()
        ]);

        // 2. ACT
        $this->withoutExceptionHandling();

        $response = $this->actingAs($user)->post(route('cycle.storeDetail', $cycle->id), [
            'physical_stock' => [
                $item->id => 85
            ]
        ]);

        // 3. ASSERT
        $response->assertStatus(302); 

        $this->assertDatabaseHas('cycle_count_details', [
            'cycle_count_id' => $cycle->id,
            'item_id' => $item->id,
            'system_stock_snapshot' => 100, 
            'physical_stock' => 85,         
            'difference' => -15             
        ]);
    }

    public function test_approve_cycle_count_mengubah_master_stok_dengan_benar()
    {
        // 1. ARRANGE
        // Persetujuan (Approve) dilakukan oleh Supervisor
        $user = User::factory()->create(['role_id' => 4]); 
        
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-TEST-B'], // Gunakan kode unik lainnya
            ['name' => 'Gudang Cadangan Uji Coba']
        );

        $item = Item::create([
            'sku' => 'TEST-002',
            'name' => 'Barang Uji Coba 2',
            'system_stock' => 50,
            'unit' => 'PCS'
        ]);

        $rack = Rack::create([
            'warehouse_id' => $warehouse->id, 
            'code' => 'RAK-TEST-02', 
            'qr_code' => 'QR-RAK-TEST-02', 
            'category' => 'B', 
            'is_locked' => 1
        ]);

        $cycle = CycleCount::create([
            'rack_id' => $rack->id,
            'status' => 'reviewed',
            'counted_by' => $user->id,
            'scheduled_at' => now()
        ]);

        CycleCountDetail::create([
            'cycle_count_id' => $cycle->id,
            'item_id' => $item->id,
            'system_stock_snapshot' => 50,
            'physical_stock' => 60,
            'difference' => 10
        ]);

        // 2. ACT
        $this->withoutExceptionHandling();

        $this->actingAs($user)->post(route('cycle.approve', $cycle->id));

        // 3. ASSERT
        $this->assertDatabaseHas('cycle_counts', [
            'id' => $cycle->id,
            'status' => 'approved'
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'system_stock' => 60
        ]);

        $this->assertDatabaseHas('racks', [
            'id' => $rack->id,
            'is_locked' => 0
        ]);
    }
}

/* 

1. Sistem berhasil mengunci data snapshot sehingga stok yang dihitung staf tidak akan kacau meskipun ada transaksi barang masuk/keluar di waktu yang sama.

2. Perhitungan selisih berjalan akurat tanpa error.

3. Tombol Approve dari manajer secara otomatis memperbarui Master Item, menghilangkan kebutuhan input manual yang memakan waktu 2 minggu itu! */