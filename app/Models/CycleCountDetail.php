<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CycleCountDetail extends Model
{
     use HasFactory;

    protected $fillable = [
        'cycle_count_id',
        'item_id',
        'system_stock_snapshot',
        'physical_stock',
        'difference'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function cycleCount()
    {
        return $this->belongsTo(CycleCount::class);
    }
}

