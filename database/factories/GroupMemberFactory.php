<?php

namespace Database\Factories;

use App\Models\GroupMember;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GroupMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'join_date' => $this->faker->date(),
            'group_id' => \App\Models\Group::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
