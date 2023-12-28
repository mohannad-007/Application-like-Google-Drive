<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'extension' => $this->faker->text(255),
            'path' => $this->faker->text(255),
            'is_active' => $this->faker->boolean(),
            'is_reserved' => $this->faker->boolean(),
            'group_id' => \App\Models\Group::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
