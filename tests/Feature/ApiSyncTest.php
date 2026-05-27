<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;

class ApiSyncTest extends TestCase
{
    // Gunakan DatabaseTransactions agar tabel aslimu aman
    use DatabaseTransactions;

    public function test_endpoint_sync_menolak_akses_tanpa_token_valid()
    {
        // 1. ACT: Aplikasi mencoba akses API tanpa menyertakan token
        $response = $this->getJson('/api/sync/pull');

        // 2. ASSERT: Server harus menolak dengan status 401 (Unauthorized)
        $response->assertStatus(401);
    }

    public function test_endpoint_sync_mengembalikan_data_dengan_token_valid()
    {
        $user = User::factory()->create(['role_id' => 5]);
        
        // JIKA PAKAI JWT AUTH, GANTI CARA BIKIN TOKENNYA JADI SEPERTI INI:
        $token = auth('api')->login($user); 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/sync/pull');

        $response->assertStatus(200);
        $response->assertJsonStructure(['my_tasks']);
    }

    public function test_endpoint_push_berhasil_menyimpan_data_dari_mobile()
    {
        // 1. ARRANGE
        $user = User::factory()->create(['role_id' => 5]);
        
        // MENGGUNAKAN JWT (Sama seperti Test 2)
        $token = auth('api')->login($user); 

        // Buat data dummy agar dipastikan datanya ada saat diuji
        $warehouse = \App\Models\Warehouse::firstOrCreate(
            ['code' => 'WH-SYNC-01'],
            ['name' => 'Gudang Sync Uji Coba']
        );

        $rack = \App\Models\Rack::create([
            'warehouse_id' => $warehouse->id,
            'code' => 'RAK-SYNC-01',
            'qr_code' => 'QR-SYNC-01',
            'category' => 'A',
            'is_locked' => 0
        ]);

        $item = \App\Models\Item::create([
            'sku' => 'TEST-SYNC-001',
            'name' => 'Barang Sync Uji Coba',
            'system_stock' => 100,
            'unit' => 'PCS'
        ]);

        $cycle = \App\Models\CycleCount::create([
            'rack_id' => $rack->id,
            'status' => 'draft',
            'counted_by' => $user->id,
            'scheduled_at' => now(),
            'started_at' => now()
        ]);

        // 2. ACT: Simulasi aplikasi Flutter mengirim JSON hasil hitungan (Push Sync)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/sync/push', [
            'cycle_counts' => [
                [
                    // Pastikan menyertakan ID jadwalnya
                    'id' => $cycle->id, 
                    
                    // Kolom-kolom yang diminta oleh validasi Laravel
                    'rack_id' => $rack->id,
                    'started_at' => now()->toDateTimeString(),
                    'finished_at' => now()->toDateTimeString(),
                    
                    // Data hitungan fisik masuk ke dalam array 'details'
                    'details' => [
                        [
                            'item_id' => $item->id,
                            'physical_stock' => 125
                        ]
                    ]
                ]
            ]
        ]);

        // 3. ASSERT: Server harus merespon berhasil
        $response->assertStatus(200);

        // Memastikan data yang didorong dari mobile benar-benar masuk ke tabel detail
        $this->assertDatabaseHas('cycle_count_details', [
            'cycle_count_id' => $cycle->id,
            'item_id' => $item->id,
            'physical_stock' => 125
        ]);
    }
}

/* 
✓ endpoint sync menolak akses tanpa token valid                                                                0.41s
✓ endpoint sync mengembalikan data dengan token valid                                                          0.13s
✓ endpoint push berhasil menyimpan data dari mobile                                                            0.10s
*/