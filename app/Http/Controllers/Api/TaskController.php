<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;
use Illuminate\Http\JsonResponse;
use App\Repository\TaskRepositoryInterface;

class TaskController extends Controller
{

    private $task;

    public function __construct(TaskRepositoryInterface $task)
    {
        $this->task = $task;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->task->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TaskRequest $request): JsonResponse
    {
        return $this->task->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        return $this->task->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TaskRequest $request, $id): JsonResponse
    {
        return $this->task->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->task->delete($id);
    }
}
