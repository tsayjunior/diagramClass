<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Methods extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'clase_id',
    ];
    // Relación inversa (muchos a uno)
    public function clase()
    {
        return $this->belongsTo(Clase::class);
    }
}
