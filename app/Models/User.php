<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'birth_date',
        'status',
        'expired_date',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'expired_date'  => 'date',
        'birth_date' => 'date',
    ];

    // Mutator: hasheia a password automaticamente
    public function setSenhaAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // JWTSubject
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}