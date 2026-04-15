<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'telefone',
        'data_nascimento',
        'status',
        'data_expiracao',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_expiracao' => 'date',
    ];

    public function setSenhaAttribute(string $value): void
    {
        $this->attributes['senha'] = Hash::make($value);
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}