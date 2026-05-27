<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Models\Item;
use App\Models\CycleCount;
use App\Models\CycleCountDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CycleCountController extends Controller
{
public function startCycle($rack_id)
{
    $rack = Rack::findOrFail($rack_id);

    if ($rack->is_locked) {
        return response()->json(['error' => 'Rack sedang dihitung'], 400);
    }

    $rack->update(['is_locked' => true]);

    return response()->json(['message' => 'Cycle started']);
}

public function storeDetail(Request $request)
{
    $item = Item::findOrFail($request->item_id);

    $difference = $request->physical_stock - $item->system_stock;

    CycleCountDetail::create([
        'cycle_count_id' => $request->cycle_count_id,
        'item_id' => $item->id,
        'system_stock_snapshot' => $item->system_stock,
        'physical_stock' => $request->physical_stock,
        'difference' => $difference
    ]);

    return response()->json([
        'message' => 'Detail saved'
    ]);
}

public function exportAdjustment($cycle_id)
{
    $details = CycleCountDetail::where('cycle_count_id', $cycle_id)
        ->where('difference', '!=', 0)
        ->get();

    $fileName = 'adjustment_'.$cycle_id.'.csv';
    $filePath = storage_path($fileName);

    $handle = fopen($filePath, 'w');

    fputcsv($handle, ['SKU', 'Adjustment']);

    foreach ($details as $d) {
        fputcsv($handle, [
            $d->item->sku,
            $d->difference
        ]);
    }

    fclose($handle);

    return response()->download($filePath)->deleteFileAfterSend(true);
}
}