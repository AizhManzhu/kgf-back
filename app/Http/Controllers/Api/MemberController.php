<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Models\EventMember;
use App\Repository\MemberRepositoryInterface;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{

    private $member;

    public function __construct(MemberRepositoryInterface $member)
    {
        $this->member = $member;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $result = $this->member->read();
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MemberRequest $request
     * @return JsonResponse
     */
    public function store(MemberRequest $request): JsonResponse
    {
        try {
            $result = $this->member->create($request->all());
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
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
        try {
            $result = $this->member->readById($id);
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MemberRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(MemberRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->member->update($id, $request->all());
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->member->delete($id);
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
        }
    }

    public function findByEventMemberId(int $eventMemberId, $token): JsonResponse
    {
        if (md5($eventMemberId."A12#21)") !== $token) {
            return $this->handleError("Token is invalid", null,200);
        }

        try {
            $member = $this->member->getByEventMemberId($eventMemberId);
            return $this->handleResponse($member);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
        }
    }

    public function setMemberCame(int $eventMemberId, string $token): JsonResponse
    {
        if (md5($eventMemberId."A12#21)") !== $token) {
            return $this->handleError("Token is invalid", null,200);
        }

        try {
            $eventMember = EventMember::query()->find($eventMemberId);
            $eventMember->came = 1;
            $eventMember->save();
            return $this->handleResponse(1);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), null,200);
        }
    }

    public function getMemberEvents($id): JsonResponse
    {
        try {
            $member = $this->member->readById($id);
            $member->load('eventMembers');
            return $this->handleResponse($member->eventMembers);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), ' ', 200);
        }
    }

}
