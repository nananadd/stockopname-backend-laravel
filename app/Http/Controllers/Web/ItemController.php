<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Rack;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemsImport;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        // UBAH 'rack' menjadi 'racks' sesuai nama fungsi di Model
        $query = Item::with('racks'); 

        // FITUR SEARCH: Mencari berdasarkan Nama Barang atau SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // FITUR FILTER: Menyaring barang berdasarkan Lokasi Rak tertentu
        if ($request->filled('rack_id')) {
            // UBAH whereHas menjadi 'racks'
            $query->whereHas('racks', function($q) use ($request) {
                $q->where('racks.id', $request->rack_id); 
            });
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $racks = \App\Models\Rack::orderBy('code', 'asc')->get();

        return view('items.index', compact('items', 'racks'));
    }

    // CRUD
    public function create()
    {
        $racks = Rack::orderBy('code', 'asc')->get();
        return view('items.create', compact('racks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:items,sku|max:100',
            'name' => 'required|max:255',
            'rack_id' => 'nullable|exists:racks,id',
            'system_stock' => 'required|integer',
        ]);

        // 1. Simpan data Item (Kecualikan rack_id karena bukan kolom di tabel items)
        $item = Item::create($request->except('rack_id'));

        // 2. Jika user memilih rak, simpan relasinya ke tabel pivot item_rack
        if ($request->filled('rack_id')) {
            $item->racks()->attach($request->rack_id);
        }

        return redirect()->route('items.index')->with('success', 'Data Barang berhasil ditambahkan!');
    }

    public function edit(Item $item)
    {
        $racks = Rack::orderBy('code', 'asc')->get();
        return view('items.edit', compact('item', 'racks'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'sku' => 'required|max:100|unique:items,sku,' . $item->id,
            'name' => 'required|max:255',
            'rack_id' => 'nullable|exists:racks,id',
            'system_stock' => 'required|integer',
        ]);

        // 1. Update data Item
        $item->update($request->except('rack_id'));

        // 2. Sinkronisasi rak di tabel pivot
        if ($request->filled('rack_id')) {
            // sync() akan otomatis menghapus rak lama dan menggantinya dengan rak baru
            $item->racks()->sync([$request->rack_id]);
        } else {
            // Jika dropdown dikosongkan, hapus semua relasi rak untuk barang ini
            $item->racks()->detach();
        }

        return redirect()->route('items.index')->with('success', 'Data Barang berhasil diperbarui!');
    }

    public function import(Request $request)
    {
        // 1. Validasi file yang diunggah
        $request->validate([
            'file_accurate' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            // 2. Eksekusi proses import menggunakan class ItemsImport yang kita buat sebelumnya
            Excel::import(new ItemsImport, $request->file('file_accurate'));

            // 3. Kembalikan dengan pesan sukses
            return redirect()->route('items.index')->with('success', 'Ribuan data Master Barang dari Accurate berhasil di-import dan disinkronisasi!');
            
        } catch (\Exception $e) {
            // Tangkap error jika format Excel tidak sesuai
            return redirect()->back()->with('error', 'Gagal melakukan import. Pastikan format file sesuai. Error: ' . $e->getMessage());
        }
    }


    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Data Barang berhasil dihapus!');
    }

    public function sync()
    {
        try {
            // TULIS LOGIKA TARIK DATA API ACCURATE DI SINI
            // Contoh: $accurateApi->fetchItems();
            
            // Jangan lupa catat ke log aktivitas Admin
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Sync Accurate',
                'description' => 'Melakukan sinkronisasi master data barang dari Accurate Online.'
            ]);

            return redirect()->back()->with('success', 'Master data barang berhasil disinkronisasi dari Accurate Online!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
        }
    }
}