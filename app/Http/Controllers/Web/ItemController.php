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
        $query = Item::with(['racks' => function($q) {
            $q->wherePivot('stock_at_location', '>', 0);
        }]);

        $query->where('system_stock', '>', 0);

        // search berdasarkan Nama Barang atau SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // filter barang berdasarkan Lokasi Rak tertentu
        if ($request->filled('rack_id')) {
            // whereHas: mastikan item ini punya stok fisik di rak yang dipilih
            $query->whereHas('racks', function($q) use ($request) {
                $q->where('racks.id', $request->rack_id)
                  ->where('item_rack.stock_at_location', '>', 0);
            })->with(['racks' => function($q) use ($request) {
                // Override relasi layar: Pastikan yang di-load ke layar cuma rak yg dipilih
                $q->where('racks.id', $request->rack_id)
                  ->wherePivot('stock_at_location', '>', 0);
            }]);
        }

        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $racks = Rack::orderBy('code')->get();
        $units = Item::select('unit')->distinct()->orderBy('unit')->pluck('unit');

        return view('items.index', compact('items', 'racks', 'units'));
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

        // Simpan data Item (Kecualikan rack_id karena bukan kolom di tabel items)
        $item = Item::create($request->except('rack_id'));

        // Jika user memilih rak, simpan relasinya ke tabel pivot item_rack
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

        // Update data Item
        $item->update($request->except('rack_id'));

        // Sinkronisasi rak di tabel pivot
        if ($request->filled('rack_id')) {
            $item->racks()->sync([$request->rack_id]);
        } else {
            $item->racks()->detach();
        }

        return redirect()->route('items.index')->with('success', 'Data Barang berhasil diperbarui!');
    }

    public function import(Request $request)
    {
        // Validasi file yang diunggah
        $request->validate([
            'file_accurate' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        ini_set('max_execution_time', 300);

        try {
            Excel::import(new ItemsImport, $request->file('file_accurate'));
            return redirect()->route('items.index')->with('success', 'Ribuan data Master Barang dari Accurate berhasil di-import dan disinkronisasi!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan import. Pastikan format file sesuai. Error: ' . $e->getMessage());
        }
    }


    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Data Barang berhasil dihapus!');
    }
}