<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'senha' => 'password',
            'telefone' => fake()->phoneNumber(),
            'data_nascimento' => fake()->date(),
            'status' => 'ativo',
            'data_expiracao' => fake()->date(),
        ];
    }
}