<?php

namespace Database\Factories;

use App\Models\FileEvent;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FileEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'details' => $this->faker->sentence(20),
            'file_id' => \App\Models\File::factory(),
            'event_type_id' => \App\Models\EventType::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
