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
    public function index()
    {
        $user = auth()->user();

        // Jika yang login adalah STAFF, hanya tampilkan tugas yang di-assign ke dia
        if ($user->role_id == 5) {
            $cycles = CycleCount::with('rack')
                ->where('counted_by', $user->id)
                ->orderBy('scheduled_at', 'asc')
                ->paginate(10);
        } else {
            // Jika Manager (3) atau Supervisor (4)
            $cycles = CycleCount::with(['rack', 'counter'])
                ->latest('started_at') 
                ->paginate(10);
        }

        return view('cycle.index', compact('cycles'));
    }

    public function show($id)
    {
        $cycle = CycleCount::with(['rack', 'details.item'])
            ->findOrFail($id);

        return view('cycle.show', compact('cycle'));
    }

    public function createSchedule()
    {
        // Ambil semua rak (baik yang terkunci maupun tidak, karena ini untuk jadwal masa depan)
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

        // dd(($request->all()));
        foreach ($request->physical_stock as $itemId => $physical) {

            $physical = $physical ? (int)$physical : 0; //biar ga null

            $item = Item::findOrFail($itemId);

            $difference = $physical - $item->system_stock;

            CycleCountDetail::updateOrCreate(
                [
                    'cycle_count_id' => $cycle->id,
                    'item_id' => $itemId
                ],
                [
                    'system_stock_snapshot' => $item->system_stock,
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
        // Ambil data cycle beserta detail barang dan raknya
        $cycle = CycleCount::with(['details.item', 'rack'])->findOrFail($id);

        // dd($cycle->details->toArray());
        // Ubah status laporan
        $cycle->status = 'approved';
        $cycle->approved_by = auth()->id(); // Catat ID supervisor
        $cycle->save();

        // MESIN UPDATE STOK OTOMATIS
        foreach ($cycle->details as $detail) {
            $item = $detail->item;
            
            if ($item) {
                // Update Master Stok (Sistem Inventory Pusat)
                $item->system_stock = ($item->system_stock - $detail->system_stock_snapshot) + $detail->physical_stock;
                $item->save();

                // LOGIKA PINDAH RAK (DEDICATED LOCATION)
                if ($detail->physical_stock == 0) {
                    // Jika staf melaporkan barang ini habis (0), hapus relasinya dari tabel 
                    // agar database item_rack tidak dipenuhi oleh baris berangka 0.
                    DB::table('item_rack')->where('item_id', $item->id)->delete();
                } else {
                    // Cek apakah barang ini sebelumnya sudah punya rumah (di rak manapun)
                    $punyaRumahLama = DB::table('item_rack')->where('item_id', $item->id)->exists();

                    if ($punyaRumahLama) {
                        // temukan datanya, lalu TIMPA 'rack_id' lamanya dengan 'rack_id' yang baru
                        // Jadi datanya tidak double, melainkan resmi pindah rak.
                        DB::table('item_rack')
                            ->where('item_id', $item->id)
                            ->update([
                                'rack_id' => $cycle->rack_id, 
                                'stock_at_location' => $detail->physical_stock,
                                'updated_at' => now(),
                            ]);
                    } else {
                        // KASUS BARANG BARU: Belum pernah masuk rak manapun sebelumnya
                        DB::table('item_rack')->insert([
                            'rack_id' => $cycle->rack_id,
                            'item_id' => $item->id,
                            'stock_at_location' => $detail->physical_stock,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // URUSAN RAK: BUKA GEMBOK & RESET JADWAL (Tempat yang Benar)
        if ($cycle->rack) {
            $cycle->rack->update([
                'is_locked' => 0,
                'last_counted_at' => now(),  // Kasi tau Cron Job bahwa rak ini baru saja dihitung
            ]);
        }

        // Catat ke log aktivitas
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'approve_cycle_count',
            'description' => "Supervisor menyetujui hitungan Rak {$cycle->rack->code}. Relasi barang nyasar otomatis dibuat jika ada."
        ]);

        return redirect()->route('cycle.index')
            ->with('success', 'Laporan disetujui! Database rak dan stok telah diperbarui (termasuk barang nyasar).');
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
            
            // Ambil pesan output dari command tersebut (opsional)
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