<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Rack;
use Illuminate\Support\Facades\DB;

class PutawaySeeder extends Seeder
{
    /**
     * Run the database seeds. php artisan db:seed --class=PutawaySeeder
     */
    public function run(): void
    {
        $items = Item::where('system_stock', '>', 0)->get();
        
        $racks = Rack::all();

        if ($items->isEmpty()) {
            $this->command->warn('Gagal Seeding: Data Items dengan stok > 0 tidak ditemukan.');
            return;
        }

        if ($racks->isEmpty()) {
            $this->command->warn('Gagal Seeding: Data Master Racks masih kosong.');
            return;
        }

        $this->command->info('Memulai pengisian data alokasi barang (Putaway) ke rak secara acak...');

        DB::transaction(function () use ($items, $racks) {
            foreach ($items as $item) {
                $randomRack = $racks->random();

                $qtyToPut = rand(1, $item->system_stock);

                $randomRack->items()->syncWithoutDetaching([
                    $item->id => [
                        'stock_at_location' => $qtyToPut,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        });

        $this->command->info('Seeding berhasil! Berhasil mengalokasikan ' . $items->count() . ' item ke rak secara acak.');
    }
}