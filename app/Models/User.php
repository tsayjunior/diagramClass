<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    static $USER_HOST = 1; //COMPRA COMPLETADA
    static $USER_GHEST = 2; //COMPRA PENDIENTE
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lastname',
        'ci',
        'type_user',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function getCreatedAtAttribute($value)
    {
        // Parsear el valor original de la fecha y ajustarlo a la zona horaria deseada
        $fechaCarbon = Carbon::parse($value)->setTimezone('America/La_Paz');
        
        // Retornar la fecha en el formato 'Y-m-d'
        return $fechaCarbon->format('Y-m-d');
    }
    public function salas()
    {
        return $this->hasMany(Sala::class, 'host_id', 'id');
    }
    public function salas_guest(){
        return $this->belongsToMany(Sala::class, 'user_sala', 'guest_id', 'sala_id');
    }
}
