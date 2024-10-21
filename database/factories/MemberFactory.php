<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'username' => $this->faker->unique()->userName,
            'telegram_id' => null,
            'phone' => $this->faker->unique()->phoneNumber,
            'email' => $this->faker->unique()->email,
            'position' => $this->faker->jobTitle,
            'company' => $this->faker->company,
            'is_checked' => rand(0, 1),
            'is_active' => rand(0,1),
        ];
    }
}
