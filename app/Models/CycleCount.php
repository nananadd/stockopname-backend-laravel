<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CycleCount extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
    'rack_id',
    'status',
    'started_at',
    'counted_by',
    'reviewed_by',
    'finished_at',
    'scheduled_at',
    'notes'
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }
    public function details()
    {
        return $this->hasMany(CycleCountDetail::class);
    }

    public function counter()
    {
        // menggunakan kolom 'counted_by' sebagai kunci asalnya."
        return $this->belongsTo(User::class, 'counted_by');
    }
}
