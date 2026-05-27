<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    // Daftarkan kolom yang boleh diisi (mass assignable)
    protected $fillable = [
        'warehouse_id',
        'code',
        'category',
        'last_counted_at',  
        'qr_code',
        'is_locked'
    ];

    public function cycleCounts()
    {
        return $this->hasMany(CycleCount::class);
    }

    // Relasi Many-to-Many ke tabel Items melalui pivot item_rack
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_rack')
                    ->withPivot('stock_at_location')
                    ->withTimestamps();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}