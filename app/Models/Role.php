<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    // Relasi One-to-Many: Satu role bisa dimiliki banyak user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}