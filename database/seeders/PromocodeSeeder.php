<?php

namespace Database\Seeders;

use App\Models\BaseKeyboard;
use App\Models\Field;
use App\Models\Promocode;
use App\Models\User;
use Database\Factories\PromocodeFactory;
use Illuminate\Database\Seeder;

class PromocodeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Promocode::factory()->count(100)->create();
    }
}
