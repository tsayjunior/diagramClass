<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRelationship extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'clase_init_id',
        'clase_finish_id',
        'type_relationship_id',
        'quantity_init',
        'quantity_finish',
        'code_sala',
        'tabla_intermedia_identificador',
    ];

    public function clase_init()
    {
        return $this->belongsTo(Clase::class, 'clase_init_id', 'id');
    }
    public function clase_finish()
    {
        return $this->belongsTo(Clase::class, 'clase_finish_id', 'id');
    }
}
