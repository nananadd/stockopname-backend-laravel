<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    const UPDATED_AT = null;

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function cycleCounts()
    {
        return $this->hasMany(CycleCount::class);
    }
}