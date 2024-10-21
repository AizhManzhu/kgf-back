<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    const ATTRIBUTES = [
        'first_name' => 'имя',
        'last_name' => 'фамилия',
        'phone' => 'номер телефона',
        'email' => 'email',
        'company' => 'компания',
        'position' => 'должность'
    ];

    const ATTRIBUTES_TEXT = [
        'first_name' => 'Введите имя',
        'last_name' => 'Введите фамилию',
        'phone' => 'Введите номер телефона',
        'email' => 'Введите email',
        'company' => 'Введите компанию',
        'position' => 'Введите должность'
    ];

    const BASE_KEYBOARD = [
        [
            'name' => 'Кто в эфире',
            'type' => 'ether',
        ],
        [
            'name' => 'Программа',
            'type' => 'program'
        ],
        [
            'name' => 'Участвую в конкурсе Botan',
            'type' => 'competition'
        ],

    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        foreach(self::BASE_KEYBOARD as $keyboard) {
//            BaseKeyboard::updateOrcreate($keyboard);
//        }

        if (env('APP_ENV') === 'local') {
            $this->call([
                RoleSeeder::class,
                PermissionSeeder::class,
                EventSeeder::class,
                MemberSeeder::class,
            ]);
        }
    }
}
