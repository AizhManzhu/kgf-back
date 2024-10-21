<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\MemberImport;
use App\Repository\EventRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->middleware('permission:read-event', ['only' => ['show']]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->eventRepository->getById($id);
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function eventMembers(int $id): JsonResponse
    {
        try {
            $eventMembers = $this->eventRepository->getCurrentMembers($id);
            return $this->handleResponse($eventMembers);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }
    public function deleteEventMember(int $eventMemberId): JsonResponse
    {
        return $this->eventRepository->deleteEventMember($eventMemberId);
    }

    public function getEventProgram($id): JsonResponse {
        try {
            $event = $this->eventRepository->getById($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $event->image
                ],
                'error' => null
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function getEventSpeakers($id): JsonResponse {
        try {
            $event = $this->eventRepository->getById($id);
            $event->load('speakers');
            return response()->json([
                'success' => true,
                'data' => [
                    'speakers' => $event->speakers,
                ],
                'error' => null
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function generatePayment($eventMemberId)
    {
        $url = config('kaspi.url');
        $data = [
            'TranId' => "$eventMemberId",
            'OrderId' => "$eventMemberId",
            'Service' => config('kaspi.service'),
            'returnUrl' => "https://kgforum.kz",
            'refererHost' => 'kgf.cic.kz',
            'GenerateQrCode' => true,
        ];
        try {
            return Curl::to($url)
                ->withContentType('application/json')
                ->withTimeout(3 * 10)
                ->withData($data)
                ->asJson()
                ->post();
        }catch (\Exception $e) {
            return $this->handleError($e->getMessage(), '', 200);
        }

    }

    public function importMember(Request $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return $this->handleError("Please attach file", 200);
        }
        Excel::import(new MemberImport(), $request->file);
        return $this->handleResponse("Success");
    }
}
