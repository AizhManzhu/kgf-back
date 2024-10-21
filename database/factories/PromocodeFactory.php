<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromocodeFactory extends Factory
{
    public function definition()
    {
        return [
            'code' => strtoupper(substr($this->faker->md5, 0, 6))
        ];
    }
}
