<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\FieldVipValues;
use App\Repository\EventRepository;
use App\Repository\TelegramRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\EventRequest;
use App\Http\Controllers\Controller;
use App\Models\FieldValues;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repository\EventRepositoryInterface;
use App\Models\Member;

class EventController extends Controller
{
    private EventRepositoryInterface $event;
    protected array $rules = [
        'program_image' => 'required'
    ];

    public function __construct(EventRepositoryInterface $event)
    {
        $this->event = $event;
        $this->middleware('permission:read-event', ['only' => ['show']]);
        $this->middleware('permission:create-event', ['only' => ['store']]);
        $this->middleware('permission:update-event', ['only' => ['update']]);
        $this->middleware('permission:delete-event', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->event->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EventRequest $request
     * @return JsonResponse
     */
    public function store(EventRequest $request): JsonResponse
    {
        return $this->event->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->event->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EventRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(EventRequest $request, int $id): JsonResponse
    {
        return $this->event->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->event->delete($id);
    }

    /**
     * Add member to event
     *
     * @param int $eventId
     * @param int $memberId
     * @return JsonResponse
     */
    public function addMember(int $eventId, int $memberId): JsonResponse
    {
        return $this->event->addMember($eventId, $memberId);
    }

    public function deleteMember(int $eventId, int $memberId): JsonResponse
    {
        return $this->event->deleteMember($eventId, $memberId);
    }

    /**
     * Add field to event
     * @param int $eventId
     * @param int $fieldId
     * @return JsonResponse
     */
    public function addField(int $eventId, int $fieldId): JsonResponse
    {
        return $this->event->addField($eventId, $fieldId);
    }

    /**
     * Upload event program
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadProgram(int $id, Request $request): JsonResponse
    {
        $this->event->checkExistOrMake("app/public/thumbnail");
        $this->event->checkExistOrMake("app/public/uploads");
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->getMessageBag()
            ]);
        }
        $event = $this->event->uploadImage($request->program_image);
        if (gettype($event) != 'string') {
            return $event;
        }
        return $this->event->attachImage($id, $event);
    }

    /**
     * Get current event speaker
     * @return JsonResponse
     */
    public function getCurrentSpeaker(): JsonResponse
    {
        return $this->event->getCurrentSpeaker();
    }

    /**
     * Activate event member by list of members with only id
     * @param int $eventId
     * @param Request $request
     * @return JsonResponse
     */
    public function activateMember(int $eventId, Request $request): JsonResponse
    {
        try {
            $eventMembers = $this->event->activateEventMembers($eventId, $request->members);
            foreach($eventMembers as $eventMember) {
                (new TelegramRepository())->sendActivatedMessage($eventMember->member, $eventMember);
            }
            $currentMembers = $this->event->getCurrentMembers($eventId);
            return $this->handleResponse($currentMembers);
        } catch (\Exception $e) {
            Log::debug('activate-member');
            Log::debug($e->getMessage());
            return $this->handleError($e->getMessage());
        }
    }

    public function getFieldsByMemberId(EventRepositoryInterface $eventRepository, int $memberId): JsonResponse
    {
        try{
            $eventId = ($eventRepository->getCurrentEvent())->id;
            $fieldsOfMember = $this->event->getFieldsByMemberId($eventId, $memberId);
            return $this->handleResponse($fieldsOfMember);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function editMemberFieldValues(int $memberId, Request $request): JsonResponse
    {
         try{
             $member = Member::find($memberId);
             if (!$request->is_vip) {
                 $fieldValues = FieldValues::query()->where('telegram_id', $member->telegram_id)->where('field_id', $request->field_id)->first();
             } else {
                 $fieldValues = FieldVipValues::query()->where('email', $member->email)->where('field_id', $request->field_id)->first();
             }

             $eventMember = EventMember::where('event_id', (new EventRepository(new Event()))->getCurrentEvent()->id)->where('member_id', $member->id)->first();

             if (empty($eventMember->member_data)) {
                 $eventMember->member_data = json_encode([
                     'changed_fields' => [$request->field_id => [
                         'field_id' => $request->field_id,
                         'old_value' => $fieldValues->value,
                         'new_value' => $request['answer'],
                     ]],
                 ]);
             } else {
                 $memberData = json_decode($eventMember->member_data);
                 if (!isset($memberData->changed_fields)) {
                     $eventMember->member_data = json_encode([
                         'changed_fields' => [$request->field_id => [
                             'field_id' => $request->field_id,
                             'old_value' => $fieldValues->value,
                             'new_value' => $request['answer'],
                         ]],
                     ]);
                 } else {
                     $changedFields = collect($memberData)->toArray()['changed_fields'];
                     $fields = collect($changedFields)->toArray();
                     if(isset($fields[$request->field_id])) {
                         $fields[$request->field_id]->field_id = $request->field_id;
                         $fields[$request->field_id]->old_value = $fieldValues->value;
                         $fields[$request->field_id]->new_value = $request->answer;
                     } else {
                         $fields[$request->field_id] = [
                             'field_id' => $request->field_id,
                             'old_value' => $fieldValues->value,
                             'new_value' => $request->answer
                         ];
                     }
                     $eventMember->member_data = json_encode(['changed_fields' => $fields]);
                 }
             }

             $eventMember->save();

             $fieldValues->value = $request['answer'];
             $fieldValues->save();
            return $this->handleResponse('');
         } catch(\Exception $e) {
            return $this->handleError($e->getMessage());
         }
    }

    public function getProgram(Request $request, EventRepositoryInterface $eventRepository): JsonResponse
    {
        $token = "-La199#";
        if ($request->token != $token) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => "Invalid token"
            ]);
        }
        $event = $eventRepository->getCurrentEvent();
        return response()->json([
            'success' => true,
            'data' => [
                'path' => $event->image
            ],
            'error' => null
        ]);
    }

    public function downloadProgram(EventRepositoryInterface $eventRepository)
    {
        $event = $eventRepository->getCurrentEvent();
        $array = explode(".", $event->image);
        $format = end($array);
        return Storage::disk('public')->download('uploads/'.$event->image, 'program.'.$format);
    }

    public function getEventCompetitions(EventRepositoryInterface $eventRepository): JsonResponse
    {
        try {
            $eventId = ($eventRepository->getCurrentEvent())->id;
            $competitions = $this->event->getEventCompetitions($eventId);
            return $this->handleResponse($competitions);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    /**
     * TODO add baseAuth
     * @param EventRepositoryInterface $eventRepository
     * @param $id
     * @return JsonResponse
     */
    public function getOptions(EventRepositoryInterface $eventRepository, $id): JsonResponse
    {
        $event = $eventRepository->getById($id);
        $collected = collect($event)->only(['need_pay', 'is_registration_open', 'show_mentor', 'show_sponsor', 'show_format', 'auto_accept', 'price']);
        return $this->handleResponse($collected->toArray());
    }
}
