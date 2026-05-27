<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rack;
use App\Models\CycleCount;
use App\Models\User;
use Carbon\Carbon;

class GenerateDailyCycleCount extends Command
{
    // Ini nama perintah yang akan kita panggil di terminal
    protected $signature = 'cyclecount:generate';
    protected $description = 'Otomatis membuat jadwal Cycle Count harian berdasarkan kategori ABC';

    public function handle()
    {
        // 1. Ambil SEMUA staf gudang (Karena sekarang hanya 1 role, misal Role = 5) yang HADIR
        $staffs = User::where('role_id', 5)
                      ->where('is_present', 1)
                      ->get();
        
        if ($staffs->isEmpty()) {
            $this->error('Tidak ada staf yang aktif!');
            return;
        }

        $staffCount = $staffs->count(); // Akan terdeteksi 10 orang
        $staffIndex = 0; 
        $tasksCreated = 0;

        // ===================================================================
        // LOGIKA RASIO PT SIGMA BERKAT SEJATI (Total 50 Rak, 10 Staf)
        // Beban kerja sangat ringan: 2 Rak per staf per hari.
        // Kuota harian otomatis menjadi 20 tugas per hari.
        // ===================================================================
        $racksPerStaff = 2; 
        $dailyQuota = $staffCount * $racksPerStaff; 

        // 2. ALGORITMA LEAST RECENTLY COUNTED
        // Ambil semua rak, urutkan dari yang BELUM PERNAH dihitung (NULL), 
        // lalu urutkan dari tanggal paling lampau.
        $racks = Rack::orderByRaw('ISNULL(last_counted_at) DESC')
                     ->orderBy('last_counted_at', 'asc')
                     ->get();

        foreach ($racks as $rack) {
            // Jika hari ini sudah bikin jadwal sesuai kuota (20 rak), STOP.
            if ($tasksCreated >= $dailyQuota) {
                break; 
            }

            $needsCounting = false;
            
            // Jika belum pernah dihitung sama sekali, WAJIB dihitung
            if (is_null($rack->last_counted_at)) {
                $needsCounting = true;
            } else {
                // Hitung selisih hari sejak terakhir dihitung
                $daysSinceLastCount = Carbon::parse($rack->last_counted_at)->diffInDays(now());

                // ATURAN ANALISIS ABC
                // Kategori A: Dihitung jika sudah lewat 30 hari
                // Kategori B: Dihitung jika sudah lewat 90 hari
                // Kategori C: Dihitung jika sudah lewat 180 hari
                if ($rack->category == 'A' && $daysSinceLastCount >= 30) $needsCounting = true;
                if ($rack->category == 'B' && $daysSinceLastCount >= 90) $needsCounting = true;
                if ($rack->category == 'C' && $daysSinceLastCount >= 180) $needsCounting = true;
            }

            // Jika rak ini jatuh tempo, buatkan jadwalnya!
            if ($needsCounting) {
                // Cek agar tidak membuat jadwal double jika draf sebelumnya belum dikerjakan
                $existingDraft = CycleCount::where('rack_id', $rack->id)
                                           ->whereIn('status', ['draft', 'recount'])
                                           ->exists();

                if (!$existingDraft) {
                    CycleCount::create([
                        'rack_id' => $rack->id,
                        'status' => 'draft',
                        // Membagi tugas bergiliran ke staf: Staf 1, Staf 2, Staf 3, kembali ke Staf 1
                        'counted_by' => $staffs[$staffIndex]->id, 
                        'scheduled_at' => now(),
                    ]);

                    // Kunci rak
                    $rack->update(['is_locked' => 1]);

                    $tasksCreated++;
                    // Pindah ke staf berikutnya
                    $staffIndex = ($staffIndex + 1) % $staffCount; 
                }
            }
        }

        $this->info("Berhasil membuat $tasksCreated jadwal Cycle Count harian baru!");
    }
}