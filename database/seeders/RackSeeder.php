<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rack;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class RackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Matikan sementara pengecekan relasi
        Schema::disableForeignKeyConstraints();

        // Kosongkan tabel dengan aman
        Rack::truncate();

        // Nyalakan lagi pengecekan relasi agar aman
        Schema::enableForeignKeyConstraints();

        for ($i = 1; $i <= 50; $i++) {
            
            $number = str_pad($i, 2, '0', STR_PAD_LEFT);
            
            if ($i <= 10) {
                $category = 'A';
            } elseif ($i <= 30) {
                $category = 'B';
            } else {
                $category = 'C';
            }

            Rack::create([
                'warehouse_id' => 1,
                'code' => 'RAK-' . $number,
                'qr_code' => 'QR-RAK-' . $number . '-' . Str::random(6),
                'category' => $category,
                'is_locked' => 0,
                'last_counted_at' => null,
            ]);
        }

        $this->command->info('Berhasil membuat 50 Rak dengan Kategori A, B, dan C!');
    }
}