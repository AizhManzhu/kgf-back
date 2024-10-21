<?php
namespace App\Services;

use App\Http\Constants;
use App\Jobs\VipMemberJob;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\FieldVipValues;
use App\Models\Member;
use App\Repository\Base;
use App\Repository\EventRepository;
use App\Repository\MemberRepository;
use App\Repository\TelegramRepository;
use http\Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Throwable;

class RegistrationService
{
    use Base;

    public string $lastName;
    public string $email;
    public string $phone;
    public string|null $promocode;
    public int $eventId;

    public array $validatedData;
    public array $fields;

    private EventRepository $eventRepository;
    private MemberRepository $memberRepository;

    private string $message = Constants::REGISTRATION_MESSAGE_ONLINE;

    private bool $pay = false;
    private bool $isPromocodeActivated = false;

    private Model $member;
    public Model $eventMember;

    public function __construct() {
        $this->eventRepository = new EventRepository(new Event());
        $this->memberRepository = new MemberRepository(new Member());
    }

    /**
     * @throws Throwable
     */
    public function registration(): void
    {
        $attributes = [
            'phone' => $this->phone,
            'email' => $this->email,
            'last_name' => $this->lastName
        ];

        $this->member = $this->memberRepository->updateOrCreate($attributes, $this->validatedData);

        $this->setEventMember();
        if ($this->promocode!=null && $this->promocode!="") {
            $promocodeService = new PromocodeService($this->promocode);
            $promocodeResult = $promocodeService->execute();
            throw_if(!$promocodeResult['is_valid'], $promocodeResult['message']);
            $this->setActivateEventMember($promocodeResult['promocode_id']);
            $this->isPromocodeActivated = true;
        }else {
            $event = $this->eventMember->load('event');
            if ($event->auto_accept) {
                $this->setActivateEventMember();
            }
        }
        if (isset($this->fields)) {
            $this->setBaseFields($this->fields);
        }
    }

    private function setActivateEventMember($promocodeId = null)
    {
        if (isset($promocodeId)) {
            $this->eventMember->promocode_id = $promocodeId;
        }
        $this->eventMember->paid = 1;
        $this->eventMember->is_activated = 1;
        $this->eventMember->save();
    }

    private function setBaseFields($fields)
    {
        foreach ($fields as $field) {
            if($field['value'] == Constants::OFFLINE) {
                $this->message = Constants::REGISTRATION_MESSAGE_OFFLINE;
                $this->pay = true;
            }
            FieldVipValues::query()->updateOrCreate(['field_id' => $field['field_id'], 'email' => $this->member->email],
                [
                    'field_id' => $field['field_id'],
                    'value' => $field['value'],
                    'event_id' => $this->eventId,
                    'email' => $this->member->email
                ]);
        }
    }

    private function setEventMember() {
        $this->eventMember = EventMember::query()->updateOrCreate([
            'event_id' => $this->eventId,
            'member_id' => $this->member->id,
            'is_registered' => 1,
            'utm' => $this->validatedData['utm'] ?? ''
        ]);
    }

    /**
     * $var data = 0,-1 or id
     *
     * 0 = user was sign up with promocode
     * -1 = user was sign up by format 'online'
     * id = user was sign up with payment
     *
     * @return JsonResponse
     */
    private function setPay(): JsonResponse
    {

        $imageService = new ImageService();
        $imageService->ticketId = $this->eventMember->id;
        $imageService->generateTicketImage();

        if ($this->pay) {
            if($this->isPromocodeActivated) {
                (new TelegramRepository())->sendActivatedMessage($this->member, $this->eventMember);
                return $this->handleResponse(0);
            } else {
                return $this->handleResponse($this->eventMember->id);
            }
        }

        VipMemberJob::dispatch($this->member->id, $this->member->first_name, $this->member->email, $this->message);

        return $this->handleResponse(-1);
    }

}
