<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Beritahu Laravel bahwa tabel ini tidak punya created_at dan updated_at
    public $timestamps = false;

    // Mengizinkan pengisian kolom name
    protected $fillable = ['name'];

    // Relasi One-to-Many: Satu role bisa dimiliki banyak user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}