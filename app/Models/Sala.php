<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code_sala',
        'host_id',
    ];
    public function guests(){
        return $this->belongsToMany(User::class, 'user_sala', 'sala_id', 'guest_id');
    }
}
