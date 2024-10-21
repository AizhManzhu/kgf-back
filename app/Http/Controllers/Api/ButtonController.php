<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ButtonRequest;
use App\Repository\ButtonRepositoryInterface;
use Illuminate\Http\JsonResponse;

class ButtonController extends Controller
{
    private $button;

    public function __construct(ButtonRepositoryInterface $button)
    {
        $this->button = $button;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->button->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ButtonRequest $request
     * @return JsonResponse
     */
    public function store(ButtonRequest $request): JsonResponse
    {
        return $this->button->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->button->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ButtonRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ButtonRequest $request, int $id): JsonResponse
    {
        return $this->button->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->button->delete($id);
    }
}
