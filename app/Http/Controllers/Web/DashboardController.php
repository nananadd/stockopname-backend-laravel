<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\Item;
use App\Models\CycleCount;
use App\Models\CycleCountDetail;    
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // DASHBOARD UMUM & SUPERVISOR (Fokus ke Operasional)
    public function index()
    {
        $rackCount = Rack::count();
        $itemCount = Item::count();
        $userRole = strtolower(auth()->user()->role->name ?? '');

        if ($userRole === 'admin') {
            return view('dashboard.admin', [
                'totalUsers' => \App\Models\User::count(),
                'totalRacks' => \App\Models\Rack::count(),
                'totalItems' => \App\Models\Item::count(),
                
                // Ambil 10 log aktivitas terbaru beserta relasi user dan rolenya
                'recentLogs' => \App\Models\ActivityLog::with(['user.role'])
                                    ->latest()
                                    ->take(10)
                                    ->get(),
                                    
                // Cek waktu terakhir tabel items diperbarui
                'lastSync'   => \App\Models\Item::latest('updated_at')->first()?->updated_at?->translatedFormat('d F Y, H:i') . ' WIB'
            ]);
        }
        
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

    // DASHBOARD MANAJER & OWNER (Fokus ke Analitik & Grafik)
    public function manager(Request $request)
    {
        // Kotak Metrik Atas
        $totalRacks = Rack::count();
        $totalItems = Item::count();
        $totalCycleCounts = CycleCount::count();
        $countsThisMonth = CycleCount::whereMonth('started_at', Carbon::now()->month)->count();

        // Data grafik line (Tren Hitungan 7 Hari Terakhir)
        $trendDates = [];
        $trendData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trendDates[] = $date->format('d M'); 
            
            // Menghitung jumlah Cycle Count pada tanggal tersebut
            $trendData[] = CycleCount::whereDate('started_at', $date)->count();
        }

        // Grafik Donut (Rasio Akurasi Stok/Variance)
        // Ambil total seluruh barang yang pernah dihitung
        $totalCountedItems = CycleCountDetail::count();

        if ($totalCountedItems > 0) {
            // Hitung yang SESUAI (stok sistem == stok fisik)
            $sesuaiCount = CycleCountDetail::whereColumn('system_stock_snapshot', 'physical_stock')->count();
            
            // Hitung yang SELISIH (stok sistem != stok fisik)
            $selisihCount = CycleCountDetail::whereColumn('system_stock_snapshot', '!=', 'physical_stock')->count();

            // Ubah ke dalam bentuk Persentase (dibulatkan agar rapi di grafik)
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

    public function admin()
    {
        $data = [
            'totalUsers' => User::count(),
            'totalRacks' => Rack::count(),
            'totalItems' => Item::count(),
            
            // Mengambil 10 log aktivitas terbaru beserta relasi user dan rolenya
            'recentLogs' => ActivityLog::with(['user.role'])
                                ->latest()
                                ->take(10)
                                ->get(),
                                
            // Mengambil sampel kapan trakhir tabel items diperbarui
            'lastSync'   => Item::latest('updated_at')->first()?->updated_at?->translatedFormat('d F Y, H:i') . ' WIB'
        ];

        return view('dashboard.admin', $data);
    }
}