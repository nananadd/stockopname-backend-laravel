<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // Daftarkan kolom yang boleh diisi (mass assignable) saat Create/Update
    protected $fillable = [
        'sku',
        'name',
        'system_stock',
        'unit',
    ];

    public function cycleDetails()
    {
        return $this->hasMany(CycleCountDetail::class);
    }

    // Relasi Many-to-Many ke tabel Racks melalui pivot item_rack
    public function racks()
    {
        return $this->belongsToMany(Rack::class, 'item_rack')
                    ->withPivot('stock_at_location')
                    ->withTimestamps();
    }
}