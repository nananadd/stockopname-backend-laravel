<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CycleCount;
use App\Models\Rack;
use App\Models\CycleCountDetail;
use App\Models\Item;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\AuthController;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Exports\LaporanCycleCountExport;
use App\Exports\AccuratePenyesuaianExport;
use Maatwebsite\Excel\Facades\Excel;

class CycleCountWebController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        $user = auth()->user();

        if ($user->role_id == 5) {

            $query = CycleCount::with('rack')
                ->where('counted_by', $user->id);

            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('rack', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $cycles = $query
                ->orderBy('scheduled_at', 'asc')
                ->paginate(10)
                ->withQueryString();

        } else {

            $query = CycleCount::with(['rack', 'counter']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('rack', function ($rack) use ($search) {
                        $rack->where('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('counter', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%");
                    });
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $cycles = $query
                ->latest('started_at')
                ->paginate(10)
                ->withQueryString();
        }

        return view('cycle.index', compact('cycles'));
    }

    public function show($id)
    {
        $cycle = CycleCount::with(['rack', 'details.item'])
            ->findOrFail($id);

         $details = CycleCountDetail::with('item')
        ->where('cycle_count_id', $id)
        ->paginate(15);

        return view('cycle.show', compact('cycle', 'details'));
    }

    public function createSchedule()
    {
        if (!in_array(auth()->user()->role_id, [1, 4])) {
            abort(403, 'Akses Ditolak');
        }
        // Ambil rak yang tidak kekunci
        $racks = Rack::where('is_locked', 0)->orderBy('code', 'asc')->get();
        
        // Ambil user yang rolenya 'staff'
        $staffs = User::where('role_id', '5')->orderBy('name', 'asc')->get();

        return view('cycle.schedule', compact('racks', 'staffs'));
    }

        public function storeSchedule(Request $request)
    {
        $request->validate([
            'rack_id' => 'required|exists:racks,id',
            'counted_by' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after_or_equal:today',
        ]);

        // Simpan jadwal dengan status 'draft'
        CycleCount::create([
            'rack_id' => $request->rack_id,
            'counted_by' => $request->counted_by,
            'scheduled_at' => $request->scheduled_at,
            'status' => 'draft',
            'started_at' => now(), // Set started_at ke waktu saat ini
        ]);

        // lock rack
        $rack = Rack::findOrFail($request->rack_id);
        $rack->update([
            'is_locked' => 1
        ]);

        return redirect()->route('cycle.index')->with('success', 'Jadwal Cycle Count berhasil dibuat!');
    }

    public function storeDetail(Request $request, $id)
    {
        $cycle = CycleCount::findOrFail($id);

        foreach ($request->physical_stock as $itemId => $physical) {

            $physical = $physical ? (int)$physical : 0; 
            
            $rackStock = DB::table('item_rack')
                ->where('rack_id', $cycle->rack_id)
                ->where('item_id', $itemId)
                ->value('stock_at_location') ?? 0;

            $difference = $physical - $rackStock;

            CycleCountDetail::updateOrCreate(
                [
                    'cycle_count_id' => $cycle->id,
                    'item_id' => $itemId
                ],
                [
                    'system_stock_snapshot' => $rackStock,
                    'physical_stock' => $physical,
                    'difference' => $difference
                ]
            );
        }

        return redirect()->route('cycle.show', $cycle->id);
    }

    public function review($id)
    {
        $cycle = CycleCount::findOrFail($id);

        // Pastikan hanya Manajer yang bisa melakukan ini (role_id = 3)
        if (auth()->user()->role_id != 3) {
            return redirect()->back()->with('error', 'Hanya Manajer yang berwenang melakukan review akhir.');
        }

        // Set kolom reviewed_by menggunakan ID manajer yang sedang login
        $cycle->update([
            'reviewed_by' => auth()->id(),
            'updated_at' => now(),
            'status' => 'reviewed'
        ]);

        // Catat ke Log Aktivitas
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'review_cycle_count',
            'description' => "Manajer memvalidasi laporan Stok Opname untuk Rak " . ($cycle->rack->code ?? 'Tidak Diketahui')
        ]);

        return redirect()->back()->with('success', 'Laporan berhasil divalidasi. Fitur Export sekarang tersedia.');
    }

    public function approve($id)
    {
        $cycle = CycleCount::with(['details.item', 'rack'])->findOrFail($id);

        $cycle->status = 'approved';
        $cycle->approved_by = auth()->id();
        $cycle->save();

        foreach ($cycle->details as $detail) {
            $item = $detail->item;

            if ($item) {
                if ($detail->physical_stock == 0) {
                    // Staf lapor kosong, hapus baris di rak tersebut
                    DB::table('item_rack')
                        ->where('item_id', $item->id)
                        ->where('rack_id', $cycle->rack_id)
                        ->delete();
                } else {
                    // Staf lapor ada isinya (bisa update jumlah, atau barang nyasar baru masuk)
                    DB::table('item_rack')->updateOrInsert(
                        ['item_id' => $item->id, 'rack_id' => $cycle->rack_id],
                        ['stock_at_location' => $detail->physical_stock, 'updated_at' => now()]
                    );
                }

                // Kalkulasi Ulang Master System Stock Berdasarkan Penjumlahan di Rak (SUM)
                $totalRealStock = DB::table('item_rack')
                                    ->where('item_id', $item->id)
                                    ->sum('stock_at_location');
                
                $item->update(['system_stock' => $totalRealStock]);
            }
        }

        // buka lock
        if ($cycle->rack) {
            $cycle->rack->update([
                'is_locked' => 0,
                'last_counted_at' => now(),  
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'approve_cycle_count',
            'description' => "Supervisor menyetujui hitungan Rak {$cycle->rack->code}."
        ]);

        return redirect()->route('cycle.index')
            ->with('success', 'Laporan disetujui! Database rak dan master stok telah diperbarui.');
    }

    // MESIN EXPORT DATA KE ACCURATE ONLINE
    public function exportAccurate($id)
    {
        $cycle = CycleCount::with(['details.item', 'rack'])->findOrFail($id);

        // Format nama file agar rapi saat didownload di laptop Manajer
        $fileName = 'Update_Accurate_Rak_' . ($cycle->rack->code ?? '') . '_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new AccuratePenyesuaianExport($cycle), $fileName);
    }

    // MESIN CETAK PDF LAPORAN
    public function exportPdf($id)
        {
        $cycle = CycleCount::with(['details.item', 'rack'])->findOrFail($id);

        // Render file view 'cycle.pdf' menjadi dokumen PDF
        $pdf = Pdf::loadView('cycle.pdf', compact('cycle'));

        // Atur ukuran kertas (A4) dan orientasi (Potrait)
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Stock_Opname_RAK_' . ($cycle->rack->code ?? '') . '.pdf');
    }

    public function exportExcelLaporan($id)
    {
        $cycle = CycleCount::with(['details.item', 'rack'])->findOrFail($id);
        
        $fileName = 'Laporan_Hitung_RAK_' . ($cycle->rack->code ?? '') . '_' . date('Ymd') . '.xlsx';

        // Memanggil class Export khusus dan mengirimkan data $cycle ke dalamnya
        return Excel::download(new LaporanCycleCountExport($cycle), $fileName);
    }

    public function syncItems($id)
    {
        $cycle = CycleCount::findOrFail($id);

        // Hanya boleh sync jika status masih dalam proses (belum di-approve)
        if (!in_array($cycle->status, ['draft', 'submitted'])) {
            return back()->with('error', 'Tidak dapat sinkronisasi. Dokumen ini sudah dikunci/disetujui.');
        }

        $rack = $cycle->rack;

        // Kumpulkan ID barang yang SAAT INI SUDAH ADA di dalam tabel laporan
        $existingItemIds = $cycle->details()->pluck('item_id')->toArray();

        // Cari barang di Rak tersebut yang BELUM MASUK ke daftar (membandingkan ID)
        $newItems = $rack->items()->whereNotIn('items.id', $existingItemIds)->get();

        $syncedCount = 0;

        // Looping untuk mengambil 'Snapshot' susulan khusus untuk barang baru tersebut
        foreach ($newItems as $item) {
            $cycle->details()->create([
                'item_id' => $item->id,
                'system_stock_snapshot' => $item->system_stock,
                'physical_stock' => 0, // Belum dihitung
                'difference' => 0 - $item->system_stock
            ]);
            $syncedCount++;
        }

        if ($syncedCount > 0) {
            return back()->with('success', "Sinkronisasi berhasil! $syncedCount barang baru ditambahkan ke daftar hitung.");
        } else {
            return back()->with('info', "Data sudah sinkron. Tidak ada barang baru yang terdeteksi di rak ini.");
        }
    }

    public function requestRecount(Request $request, $id)
    {
        $cycle = CycleCount::findOrFail($id);
        
        $cycle->update([
            'status' => 'recount',
            'notes' => $request->notes // Simpan catatan dari supervisor
        ]);

        return redirect()->back()->with('success', 'Status berhasil diubah ke Recount.');
    }

    public function runAutoGenerator()
    {
        try {
            // Memanggil command 'cyclecount:generate'
            Artisan::call('cyclecount:generate');
            
            $output = Artisan::output();

            return redirect()->back()->with('success', 'Berhasil menjalankan generator! ' . $output);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menjalankan generator: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $cycle = CycleCount::findOrFail($id);

        // KUNCI PENTING: Pastikan rak terbuka kembali agar bisa dijadwalkan lagi
        if ($cycle->rack) {
            $cycle->rack->update(['is_locked' => 0]);
        }

        // Catat ke log aktivitas sebelum data dihapus
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_cycle_count',
            'description' => "Supervisor menghapus jadwal Cycle Count untuk Rak " . ($cycle->rack->code ?? 'Tidak Diketahui')
        ]);

        // Hapus detailnya terlebih dahulu jika ada, lalu hapus headernya
        $cycle->details()->delete();
        $cycle->delete();

        return redirect()->route('cycle.index')
            ->with('success', 'Jadwal berhasil dihapus dan gembok rak telah dibuka kembali.');
    }
}