<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'id_key_go_js',
        'coordenadaX',
        'coordenadaY',
        'color',
        'code_sala',
    ];
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
    public function methods()
    {
        return $this->hasMany(Methods::class);
    }
}
