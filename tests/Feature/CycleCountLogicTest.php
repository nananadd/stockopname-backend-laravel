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

    // Skenario Sukses: Perhitungan Selisih
    public function test_perhitungan_selisih_stok_dan_snapshot_berjalan_benar()
    {
        // ARRANGE
        $user = User::factory()->create(['role_id' => 4]); 
        
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-TEST-A'],
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

        // ACT
        $this->withoutExceptionHandling();

        $response = $this->actingAs($user)->post(route('cycle.storeDetail', $cycle->id), [
            'physical_stock' => [
                $item->id => 85
            ]
        ]);

        // ASSERT
        $response->assertStatus(302); 

        $this->assertDatabaseHas('cycle_count_details', [
            'cycle_count_id' => $cycle->id,
            'item_id' => $item->id,
            'system_stock_snapshot' => 100, 
            'physical_stock' => 85,         
            'difference' => -15             
        ]);
    }

    // Skenario Sukses: Approve Mengubah Stok
    public function test_approve_cycle_count_mengubah_master_stok_dengan_benar()
    {
        // ARRANGE
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

        // ACT
        $this->withoutExceptionHandling();

        $this->actingAs($user)->post(route('cycle.approve', $cycle->id));

        // ASSERT
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

    // Skenario Keamanan (RBAC)
    public function test_staf_gudang_ditolak_saat_mencoba_melakukan_approve()
    {
        // Setup Role & User
        $roleStaff = \App\Models\Role::firstOrCreate(['id' => 5], ['name' => 'staff']);
        $staff = User::factory()->create(['role_id' => $roleStaff->id]);
        
        $warehouse = \App\Models\Warehouse::firstOrCreate(['code' => 'WH-SEC'], ['name' => 'Gudang Security']);
        $rack = \App\Models\Rack::create(['warehouse_id' => $warehouse->id, 'code' => 'RAK-SEC', 'qr_code' => 'QR-SEC', 'category' => 'A', 'is_locked' => 1]);
        
        $cycleCount = \App\Models\CycleCount::create([
            'rack_id' => $rack->id,
            'status' => 'submitted',
            'counted_by' => $staff->id,
            'started_at' => now()
        ]);

        // ACT: Staf mencoba menembak URL web khusus Supervisor
        $response = $this->actingAs($staff)->post(route('cycle.approve', $cycleCount->id));

        // ASSERT: Memastikan sistem dengan tegas menolak akses (403 Forbidden)
        $response->assertStatus(403); 
        
        // ASSERT: Memastikan status dokumen di database tetap aman dan tidak berubah
        $this->assertDatabaseHas('cycle_counts', [
            'id' => $cycleCount->id,
            'status' => 'submitted' 
        ]);
    }

    // Skenario Fungsional: Logika Penolakan (Recount)
    public function test_recount_mengembalikan_status_ke_draft_dan_menyimpan_catatan()
    {
        // Setup
        $roleSpv = \App\Models\Role::firstOrCreate(['id' => 4], ['name' => 'supervisor']);
        $supervisor = User::factory()->create(['role_id' => $roleSpv->id]);
        
        $warehouse = \App\Models\Warehouse::firstOrCreate(['code' => 'WH-REC'], ['name' => 'Gudang Recount']);
        $rack = \App\Models\Rack::create(['warehouse_id' => $warehouse->id, 'code' => 'RAK-REC', 'qr_code' => 'QR-REC', 'category' => 'A', 'is_locked' => 1]);
        
        $cycleCount = \App\Models\CycleCount::create([
            'rack_id' => $rack->id,
            'status' => 'submitted',
            'counted_by' => $supervisor->id,
            'started_at' => now()
        ]);

        // ACT: Supervisor menekan tombol Recount beserta alasan
        $response = $this->actingAs($supervisor)->post(route('cycle.recount', $cycleCount->id), [
            'notes' => 'Tolong hitung ulang rak ini, selisihnya terlalu jauh.'
        ]);

        // 302 adalah status berhasil redirect kembali ke halaman sebelumnya
        $response->assertStatus(302);
        
        // ASSERT: Dokumen kembali ke antrean staf (recount) beserta pesan revisinya
        $this->assertDatabaseHas('cycle_counts', [
            'id' => $cycleCount->id,
            'status' => 'recount',
            'notes' => 'Tolong hitung ulang rak ini, selisihnya terlalu jauh.'
        ]);
    }

    // Skenario Alur Bisnis: Penanganan Barang Nyasar (Lokasi Baru)
    public function test_approve_barang_nyasar_membuat_relasi_lokasi_baru_di_rak()
    {
        // Setup
        $roleSpv = \App\Models\Role::firstOrCreate(['id' => 4], ['name' => 'supervisor']);
        $supervisor = User::factory()->create(['role_id' => $roleSpv->id]);
        
        $warehouse = \App\Models\Warehouse::firstOrCreate(['code' => 'WH-NYS'], ['name' => 'Gudang Nyasar']);
        $rack = \App\Models\Rack::create(['warehouse_id' => $warehouse->id, 'code' => 'RAK-NYS', 'qr_code' => 'QR-NYS', 'category' => 'A', 'is_locked' => 1]);
        $item = \App\Models\Item::create(['sku' => 'TEST-NYS', 'name' => 'Barang Nyasar', 'system_stock' => 10, 'unit' => 'PCS']);

        $cycleCount = \App\Models\CycleCount::create([
            'rack_id' => $rack->id,
            'status' => 'submitted',
            'counted_by' => $supervisor->id,
            'started_at' => now()
        ]);
        
        // Mensimulasikan staf menemukan barang yang di sistem awalnya 0 di rak tersebut
        \App\Models\CycleCountDetail::create([
            'cycle_count_id' => $cycleCount->id,
            'item_id' => $item->id,
            'system_stock_snapshot' => 0, 
            'physical_stock' => 5,
            'difference' => 5
        ]);

        // ACT: Supervisor menyetujui
        $response = $this->actingAs($supervisor)->post(route('cycle.approve', $cycleCount->id));
        $response->assertStatus(302);

        // ASSERT: Sistem otomatis mendaftarkan alamat baru (barang nyasar) tersebut ke tabel pivot
        $this->assertDatabaseHas('item_rack', [
            'item_id' => $item->id,
            'rack_id' => $rack->id,
            'stock_at_location' => 5
        ]);
    }

    // Skenario Otomatisasi: Auto-Generator Berdasarkan Kehadiran Staf
    public function test_generator_tugas_hanya_diberikan_kepada_staf_berstatus_hadir()
    {
        // Bersihkan staf lama dan antrean tugas agar tidak bentrok
        \App\Models\User::where('role_id', 5)->delete(); 
        \App\Models\CycleCount::query()->delete(); 
        
        // TANDAI SEMUA RAK LAMA seolah-olah baru dihitung hari ini 
        // (Agar kuota tugas generator tidak dihabiskan oleh data dummy lama)
        \App\Models\Rack::query()->update(['last_counted_at' => now()]); 
        
        $roleStaff = \App\Models\Role::firstOrCreate(['id' => 5], ['name' => 'staff']);

        // Membuat 1 staf hadir dan 1 staf cuti
        $staffHadir = User::factory()->create(['role_id' => $roleStaff->id, 'is_present' => 1]);
        $staffCuti = User::factory()->create(['role_id' => $roleStaff->id, 'is_present' => 0]);
        
        $warehouse = \App\Models\Warehouse::firstOrCreate(['code' => 'WH-GEN'], ['name' => 'Gudang Generator']);
        
        // Membuat 1 rak baru yang BELUM pernah dihitung (last_counted_at = null)
        $rack = \App\Models\Rack::create(['warehouse_id' => $warehouse->id, 'code' => 'RAK-GEN', 'qr_code' => 'QR-GEN', 'category' => 'A', 'is_locked' => 0, 'last_counted_at' => null]);

        // ACT: Memicu perintah Cron Job pembuat jadwal otomatis
        $this->artisan('cyclecount:generate')->assertSuccessful();

        // ASSERT: Tugas dilempar ke staf yang hadir
        $this->assertDatabaseHas('cycle_counts', [
            'rack_id' => $rack->id,
            'counted_by' => $staffHadir->id 
        ]);

        // ASSERT: Sistem tidak melempar tugas ke staf yang cuti
        $this->assertDatabaseMissing('cycle_counts', [
            'counted_by' => $staffCuti->id 
        ]);
    }
}