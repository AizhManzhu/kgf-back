<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Http\Requests\InlineButtonRequest;
use App\Repository\InlineButtonRepositoryInterface;
use Illuminate\Http\JsonResponse;

class InlineButtonController extends Controller
{
    private $repository;

    public function __construct(InlineButtonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->repository->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InlineButtonRequest $request
     * @return JsonResponse
     */
    public function store(InlineButtonRequest $request): JsonResponse
    {
        return $this->repository->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->repository->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param InlineButtonRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(InlineButtonRequest $request, int $id): JsonResponse
    {
        return $this->repository->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->repository->delete($id);
    }
}
