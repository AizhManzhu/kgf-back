<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\EventMember;
use App\Repository\EventRepositoryInterface;
use Illuminate\Http\JsonResponse;

class EventMemberController extends Controller
{

    private $event;

    public function __construct(EventRepositoryInterface $event)
    {
        $this->event = $event;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $eventMembers = $this->event->getCurrentEvent()->eventMembers;
        return $this->handleResponse($eventMembers);
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

    public function eventTelegramMembers() {
        $event = Event::query()
            ->with(['eventMembers' => function($query) {
                $query->where('telegram_id','<>', null);
            }])
            ->where('is_current', 1)
            ->first();
        return  $this->handleResponse($event->eventMembers);
    }
}
