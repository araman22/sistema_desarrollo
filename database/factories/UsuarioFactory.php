<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * The model the factory corresponds to.
     *
     * @var class-string<Usuario>
     */
    protected $model = Usuario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'policia_id' => null,
            'rol_id' => Rol::query()->orderBy('id')->value('id'),
            'nombre_usuario' => fake()->unique()->userName(),
            'correo_electronico' => fake()->unique()->safeEmail(),
            'contrasena' => static::$password ??= Hash::make('1234'),
            'activo' => true,
            'ultimo_acceso' => null,
            'recordar_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
