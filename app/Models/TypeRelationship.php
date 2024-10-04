<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeRelationship extends Model
{
    use HasFactory;
    static $ASOCIATION = 'Asociacion'; 
    static $AGREGATION = 'Agregacion'; 
    static $DEPENDENCIA = 'Dependencia'; 
    static $COMPOSITION = 'Composicion'; 
    static $GENERALIZATION = 'Generalizacion'; 
    protected $fillable = [
        'name',
    ];
    public function classRelationShip()
    {
        return $this->hasMany(ClassRelationship::class);
    }
    static function getTypeRelationship($type_relationship_name)
    {
        return self::where('name', $type_relationship_name)->first();
    }
    
}
