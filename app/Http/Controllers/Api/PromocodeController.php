<?php

namespace App\Http\Controllers\Api;

use App\Repository\Base;
use App\Repository\PromocodeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PromocodeController extends Controller
{
    use Base;

    private $repository;

    public function __construct(PromocodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        return $this->handleResponse($this->repository->read());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->handleResponse($this->repository->create($request->all()));
    }

    public function show(int $id): JsonResponse
    {
        return $this->handleResponse($this->repository->readById($id));
    }

    public function update(Request $request, $id): JsonResponse
    {
        return $this->handleResponse($this->repository->update($id, $request->all()));
    }

    public function destroy($id): JsonResponse
    {
        return $this->handleResponse($this->repository->delete($id));
    }
}
