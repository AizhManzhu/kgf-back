<?php

namespace App\Repository;

use App\Models\InlineButton;
use Exception;
use Illuminate\Http\JsonResponse;

class InlineButtonRepository implements InlineButtonRepositoryInterface
{
    use Base;

    protected $model;

    public function __construct(InlineButton $model)
    {
        $this->model = $model;
    }

    public function read(): JsonResponse
    {
        try {
            $events = $this->model->query()->orderByDesc('id')->paginate(20);
            return $this->handleResponse($events);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function create($model): JsonResponse
    {
        try {
            $event = $this->model->query()->create($model);
            return $this->handleResponse($event);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function readById($id): JsonResponse
    {
        try {
            $event = $this->model->query()->find($id);
            return $this->handleResponse($event);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function update($id, $array): JsonResponse
    {
        try {
            $this->model->query()->where('id', $id)->update($array);
            return $this->handleResponse(null);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->model->query()->find($id)->delete();
            return $this->handleResponse(null);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }
}
