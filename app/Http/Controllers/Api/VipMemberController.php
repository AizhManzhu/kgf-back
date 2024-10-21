<?php

namespace App\Http\Controllers\Api;

use App\Exports\EventMembersExport;
use App\Http\Constants;
use App\Http\Controllers\Controller;
use App\Repository\Base;
use App\Repository\MemberRepositoryInterface;
use App\Repository\TelegramRepository;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\VipMemberJob;
use App\Models\EventMember;
use App\Models\Field;
use App\Models\FieldVipValues;
use App\Models\Member;
use App\Repository\EventRepositoryInterface;
use Illuminate\Support\Facades\Log;


class VipMemberController extends Controller
{
    use Base;

    /**
     * @deprecated
     */
    public function getByEmail(Request $request, MemberRepositoryInterface $memberRepository): JsonResponse {
        $selects = (new Member())->selects;
        $keyValues = $request->only(['email', 'last_name']);
        $member = $memberRepository->getByAttributes($selects, $keyValues);
        return $this->handleResponse($member);
    }

    public function getByEmailAndPhone(Request $request, MemberRepositoryInterface $memberRepository): JsonResponse {
        $selects = (new Member())->selects;
        $query = $request->query();
        unset($query['hash']);
        $member = $memberRepository->getByAttributes($selects, $query);
        return $this->handleResponse($member);
    }

    public function saveVipMembers(EventRepositoryInterface $eventRepository, Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'company' => 'required',
                'position' => 'required',
                'email' => 'required',
                'utm' => 'nullable',
            ]);

            $validatedData['phone'] = preg_replace('![^0-9]+!', '', $validatedData['phone']);

            $member = Member::query()->updateOrCreate(['email' => $validatedData['email'], 'last_name' => $validatedData['last_name']], $validatedData);

            $eventId = ($eventRepository->getCurrentEvent())->id;

            $eventMembers = EventMember::query()->updateOrCreate([
                'event_id' => $eventId,
                'member_id' => $member->id
            ],[
                'event_id' => $eventId,
                'member_id' => $member->id,
                'is_registered' => 0,
                'utm' => $validatedData['utm'] ?? ''
            ]);
            $message = Constants::REGISTRATION_MESSAGE_ONLINE;
            if (isset($request->fields)) {
                Log::debug($request->fields);
                foreach ($request->fields as $field) {
                    if($field['value'] == Constants::OFFLINE) {
                        $message = Constants::REGISTRATION_MESSAGE_OFFLINE;
                    }
                    FieldVipValues::query()->updateOrCreate(['field_id' => $field['field_id'], 'email' => $member->email],
                        [
                            'field_id' => $field['field_id'],
                            'value' => $field['value'],
                            'event_id' => $eventId,
                            'email' => $member->email
                        ]);
                }
            }
            if (env("AUTO_ACCEPT")) {
                $eventMembers->is_activated = 1;
                $eventMembers->save();
                (new TelegramRepository())->sendActivatedMessage($eventMembers->member, $eventMembers);
            }
            VipMemberJob::dispatch($member->id, $member->first_name, $member->email, $message);
            return $this->handleResponse($eventMembers->id);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
            return $this->handleError($e->getMessage());
        }
    }

    public function saveVipMembersWithPayment(EventRepositoryInterface $eventRepository, Request $request)
    {
        try {
            $pay = false;
            $rules = $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'company' => 'required',
                'position' => 'required',
                'email' => 'required',
                'utm' => 'nullable',
//                'fields.*.field_id' => 'nullable',
//                'fields.*.value' => 'nullable',
            ]);

            $rules['phone'] = preg_replace('![^0-9]+!', '', $rules['phone']);

            $isRegisteredMember = Member::query()->where('phone', 'like', "%" . substr($rules['phone'], 1))->first();


            $eventId = ($eventRepository->getCurrentEvent())->id;

            if ($isRegisteredMember) {
                $isRegisteredMember->first_name = $rules['first_name'];
                $isRegisteredMember->last_name = $rules['last_name'];
                $isRegisteredMember->phone = $rules['phone'];
                $isRegisteredMember->company = $request['company'];
                $isRegisteredMember->position = $request['position'];
                $isRegisteredMember->email = $rules['email'];
                $isRegisteredMember->save();
                $eventMembers = EventMember::query()->updateOrCreate([
                    'event_id' => $eventId,
                    'member_id' => $isRegisteredMember->id,
                    'is_registered' => 1
                ]);

            } else {
                $isRegisteredMember = Member::query()->create($rules);

                $eventMembers = new EventMember();
                $eventMembers->event_id = $eventId;
                $eventMembers->member_id = $isRegisteredMember->id;
                $eventMembers->is_registered = 1;
            }
            if (isset($rules['utm'])) {
                $eventMembers->utm = $rules['utm'];
            }
            $eventMembers->save();

            $baseFields = Field::where('is_base', 1)->get();
            foreach ($baseFields as $field) {
                foreach ($rules as $key => $value) {
                    if ($field->member_field === $key) {
                        FieldVipValues::query()->updateOrCreate(['field_id' => $field->id, 'email' => $isRegisteredMember->email],
                            [
                                'email' => $isRegisteredMember->email,
                                'field_id' => $field->id,
                                'value' => $value,
                            ]);
                    }
                }
            }

            if (isset($request->fields)) {
                foreach ($request->fields as $field) {
                    if ($field['field_id'] == EventMembersExport::FORMAT && $field['value'] == 'Оффлайн') {
                        $pay = true;
                    }
                    FieldVipValues::query()->updateOrCreate(['field_id' => $field['field_id'], 'email' => $isRegisteredMember->email],
                        [
                            'field_id' => $field['field_id'],
                            'value' => $field['value'],
                            'event_id' => $eventId,
                            'email' => $isRegisteredMember->email
                        ]);
                }
            }

            if ($pay == true) {
                $id = 990000 + $eventMembers->id;
                return $this->handleResponse($eventMembers->id);
            } else {
                $message = Constants::REGISTRATION_MESSAGE_ONLINE;
                VipMemberJob::dispatch($isRegisteredMember->id, $isRegisteredMember->first_name, $isRegisteredMember->email, $message);
            }
            return $this->handleResponse('');
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
            return $this->handleError($e->getMessage());
        }
    }

    public function registrate(Request $request): JsonResponse
    {
        try {
            $registrationService = new RegistrationService();

            $registrationService->lastName = $request->get('last_name');
            $registrationService->email = $request->get('email');
            $registrationService->phone = preg_replace('![^0-9]+!', '', $request->get('phone'))."";
            $registrationService->validatedData = $request->only([
                'first_name',
                'last_name',
                'phone',
                'company',
                'position',
                'email',
                'utm',
                'format',
                'need_mentor',
                'is_sponsor',
            ]);
            $registrationService->eventId = $request->get('id');
            $registrationService->promocode = $request->get('promocode');
            $registrationService->registration();
            return $this->handleResponse($registrationService->eventMember->id);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
            return $this->handleError($e->getMessage(), '', 200);
        } catch (\Throwable $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
            return $this->handleError($e->getMessage(), '', 200);
        }
    }
}
