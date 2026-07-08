<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => fake()->unique()->numerify('09#########'),
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_STATION_AGENT,
            'station_id' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SUPER_ADMIN,
            'station_id' => null,
        ]);
    }

    public function stationAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_STATION_AGENT,
        ]);
    }

    public function truckDriver(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_TRUCK_DRIVER,
            'station_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
