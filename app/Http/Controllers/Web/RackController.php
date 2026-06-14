<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RackController extends Controller
{
    public function index(Request $request)
    {
        $query = Rack::query();

        // Mencari berdasarkan kode rak atau QR code
        if ($request->filled('search')) {
            $search = $request->search;
            // Agar tidak merusak filter lain
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        // Filter Kategori A, B, atau C
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter status terbuka (0) atau Terkunci (1)
        if ($request->filled('is_locked')) {
            $query->where('is_locked', $request->is_locked);
        }

        // Tampilkan 10 data per halaman dan bawa parameter URL pencariannya
        $racks = $query->latest()->paginate(10)->withQueryString();

        return view('racks.index', compact('racks'));
    }

    // CRUD
    public function create()
    {
        // Ambil data gudang dari database
        $warehouses = Warehouse::orderBy('name', 'asc')->get();
        
        return view('racks.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:racks,code|max:50',
            'qr_code' => 'required|unique:racks,qr_code|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'category' => 'required|in:A,B,C',
        ]);

        Rack::create($request->all());

        return redirect()->route('racks.index')->with('success', 'Data Rak berhasil ditambahkan!');
    }

    public function edit(Rack $rack)
    {
        // Ambil semua data gudang untuk pilihan dropdown saat edit
        $warehouses = Warehouse::orderBy('name', 'asc')->get();
        
        // Lempar variabel $rack dan $warehouses ke tampilan
        return view('racks.edit', compact('rack', 'warehouses'));
    }

    public function update(Request $request, Rack $rack)
    {
        $request->validate([
            'code' => 'required|max:50|unique:racks,code,' . $rack->id,
            'qr_code' => 'required|max:255|unique:racks,qr_code,' . $rack->id,
            'warehouse_id' => 'required|exists:warehouses,id',
            'category' => 'required|in:A,B,C',
            'is_locked' => 'required|in:0,1',
        ]);

        $rack->update($request->all());

        return redirect()->route('racks.index')->with('success', 'Data Rak berhasil diperbarui!');
    }

    public function destroy(Rack $rack)
    {
        $rack->delete();
        return redirect()->route('racks.index')->with('success', 'Data Rak berhasil dihapus!');
    }

    public function show($id)
    {
        $rack = Rack::findOrFail($id);
        
        // Melempar data rak ke view (Blade)
        return view('racks.show', compact('rack'));
    }
}
