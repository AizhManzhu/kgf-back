<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpeakerRequest;
use App\Repository\EventRepositoryInterface;
use App\Repository\SpeakerRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SpeakerController extends Controller
{
    private $speaker;

    public function __construct(SpeakerRepositoryInterface $speaker)
    {
        $this->speaker = $speaker;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $result = $this->speaker->read();
            return $this->handleResponse($result);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SpeakerRequest $request
     * @return JsonResponse
     */
    public function store(SpeakerRequest $request, EventRepositoryInterface $eventRepository): JsonResponse
    {
        $name = $eventRepository->uploadImage($request->speaker_image);
        if (gettype($name) != 'string') {
            return $name;
        }
        $input = $request->all();
        $input['image_path'] = $name;
        try {
            $this->speaker->create($input);
            return $eventRepository->readById($input['event_id']);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->speaker->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SpeakerRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SpeakerRequest $request, int $id): JsonResponse
    {
        return $this->speaker->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->speaker->delete($id);
    }

    public function makeCurrent(Request $request)
    {
        try {
            $this->speaker->makeCurrent($request->id, $request->eventId);
            $speakers = $this->speaker->getCurrentEventSpeakers($request->eventId);
            return $this->handleResponse($speakers);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }
}
