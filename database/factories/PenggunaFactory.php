<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenggunaFactory extends Factory
{
    public function definition()
    {
        return [
            'nama' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'nomor_hp' => $this->faker->phoneNumber(),
            'password' => Hash::make('password123'), // Password default
            'peran' => $this->faker->randomElement(['kasir', 'koki']),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'admin',
                'username' => 'admin_' . Str::random(4),
            ];
        });
    }

    public function kasir()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'pelayan',
            ];
        });
    }

    public function koki()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'koki',
            ];
        });
    }
}