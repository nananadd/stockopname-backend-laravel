<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\Item;
use App\Models\CycleCount;
use App\Models\CycleCountDetail;    
use Illuminate\Http\Request;
use Carbon\Carbon; // buat ngambil data tanggal pada grafik

class DashboardController extends Controller
{
    // ========================================================
    // 1. DASHBOARD UMUM & SUPERVISOR (Fokus ke Operasional)
    // ========================================================
    public function index()
    {
        $rackCount = Rack::count();
        $itemCount = Item::count();
        
        // Hitung laporan berdasarkan status
        $pendingReviewCount = CycleCount::where('status', '!=', 'approved')->count();
        $approvedCount = CycleCount::where('status', 'approved')->count();

        // Ambil 5 laporan terbaru untuk ditampilkan langsung di tabel Dashboard
        $recentCycles = CycleCount::with('rack')
                        ->orderBy('started_at', 'desc')
                        ->take(5)
                        ->get();

        // Mengarah ke file resources/views/dashboard.blade.php
        return view('dashboard.index', compact(
            'rackCount', 
            'itemCount', 
            'pendingReviewCount', 
            'approvedCount',
            'recentCycles'
        ));
    }

    // ========================================================
    // 2. DASHBOARD MANAJER (Fokus ke Analitik & Grafik)
    // ========================================================
    public function manager(Request $request)
    {
        // Kotak Metrik Atas
        $totalRacks = Rack::count();
        $totalItems = Item::count();
        $totalCycleCounts = CycleCount::count();
        $countsThisMonth = CycleCount::whereMonth('started_at', Carbon::now()->month)->count();

        // DATA UNTUK GRAFIK LINE (Tren Hitungan 7 Hari Terakhir)
        $trendDates = [];
        $trendData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trendDates[] = $date->format('d M'); // Format: "14 Apr"
            
            // Menghitung jumlah Cycle Count pada tanggal tersebut
            $trendData[] = CycleCount::whereDate('started_at', $date)->count();
        }

        // =======================================================
        // DATA UNTUK GRAFIK DOUGHNUT (Rasio Akurasi Stok/Variance)
        // =======================================================
        
        // 1. Ambil total seluruh barang yang pernah dihitung
        $totalCountedItems = CycleCountDetail::count();

        if ($totalCountedItems > 0) {
            // 2. Hitung yang SESUAI (stok sistem == stok fisik)
            // nama kolom adalah 'system_stock_snapshot' dan 'physical_stock'. 
            // Silakan sesuaikan jika nama kolom di databasemu berbeda.
            $sesuaiCount = CycleCountDetail::whereColumn('system_stock_snapshot', 'physical_stock')->count();
            
            // 3. Hitung yang SELISIH (stok sistem != stok fisik)
            $selisihCount = CycleCountDetail::whereColumn('system_stock_snapshot', '!=', 'physical_stock')->count();

            // 4. Ubah ke dalam bentuk Persentase (dibulatkan agar rapi di grafik)
            $varianceSesuai = round(($sesuaiCount / $totalCountedItems) * 100);
            $varianceSelisih = round(($selisihCount / $totalCountedItems) * 100);
        } else {
            // Jika gudang masih kosong dan belum ada aktivitas hitung sama sekali
            $varianceSesuai = 0;
            $varianceSelisih = 0;
        }

        // Mengarah ke file resources/views/dashboard/manager.blade.php
        return view('dashboard.manager', compact(
            'totalRacks', 
            'totalItems', 
            'totalCycleCounts', 
            'countsThisMonth',
            'trendDates',
            'trendData',
            'varianceSesuai',
            'varianceSelisih'
        ));
    }
}