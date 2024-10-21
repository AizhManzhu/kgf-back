<?php

namespace Database\Seeders;

use App\Models\BaseKeyboard;
use App\Models\EventMember;
use App\Models\Field;
use App\Models\Member;
use App\Models\Promocode;
use App\Models\User;
use Database\Factories\MemberFactory;
use Database\Factories\PromocodeFactory;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class MemberSeeder extends Seeder
{
    use WithFaker;
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        Member::factory()->count(2000)->create();
        $members = Member::query()->get();
        foreach ($members as $member) {
            EventMember::query()->updateOrCreate([
                'event_id' => 1,
                'member_id' => $member->id
            ], [
                'event_id' => 1,
                'member_id' => $member->id,
                'is_activated' => rand(0,1),
                'is_registered' => rand(0,1),
                'format' => rand(0,1),
                'paid' => rand(0,1),
                'need_mentor' => rand(0,1),
                'is_sponsor' => rand(0,1)
            ]);
        }
    }
}
