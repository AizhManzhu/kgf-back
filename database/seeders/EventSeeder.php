<?php

namespace Database\Seeders;

use App\Models\BaseKeyboard;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Field;
use App\Models\Member;
use App\Models\Promocode;
use App\Models\User;
use Database\Factories\MemberFactory;
use Database\Factories\PromocodeFactory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class EventSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $event = Event::query()->find(1);
        if (!$event) {
            Event::query()->create([
                'title' => $this->faker->title,
                'description' => $this->faker->text,
                'address' => $this->faker->address,
                'event_date' => $this->faker->date,
                'is_current' => 1,
                'welcome_message' => $this->faker->text,
                'thank_you_message' => $this->faker->text,
                'price' => $this->faker->randomFloat(),
                'need_pay' => 1,
                'is_registration_open' => 1,
            ]);
        }
    }
}
