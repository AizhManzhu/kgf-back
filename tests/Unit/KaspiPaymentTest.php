<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase;

class KaspiPaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private array $body;
    private $eventMember;
    private $event;
    private $member;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpFaker();

        $this->event = Event::create([
            'title' => $this->faker->title,
            'description' => $this->faker->text(200),
            'address' => $this->faker->address,
            'event_date' => $this->faker->date,
            'short_name' => $this->faker->title,
            'is_current' => 0,
            'welcome_message' => $this->faker->text,
            'thank_you_message' => $this->faker->text,
            ]);
        $this->member = Member::query()->create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'username' => $this->faker->userName,
            'telegram_id' => $this->faker->randomDigit(),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'position' => $this->faker->position,
            'company' => $this->faker->company,
        ]);
        $this->eventMember = EventMember::query()->create([
            'event_id' => $this->event->id,
            'member_id' => $this->member->id,
        ]);

    }

    public function test_command_check()
    {
        $this->body = [
            'txn_id' => $this->faker->randomNumber(),
            'txn_date' => date('ymdhis', time()),
            'account' => $this->eventMember->id,
            'command' => 'check',
            'comment' => '',
            'sum' => floatval(100),
        ];
        $response = $this->post('/api/v1/kaspi/pay', $this->body);
        $response->dd();
        $response->assertStatus(200)->assertJson();

    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }
}
