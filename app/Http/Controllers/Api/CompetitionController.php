<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CompetitionRequest;
use App\Repository\CompetitionRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CompetitionController extends Controller
{
    private $competition;

    public function __construct(CompetitionRepositoryInterface $competition)
    {
        $this->competition = $competition;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->competition->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CompetitionRequest $request
     * @return JsonResponse
     */
    public function store(CompetitionRequest $request): JsonResponse
    {
        return $this->competition->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->competition->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CompetitionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CompetitionRequest $request, int $id): JsonResponse
    {
        return $this->competition->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->competition->delete($id);
    }
}



