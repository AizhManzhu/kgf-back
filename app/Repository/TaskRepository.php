<?php
namespace App\Repository;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Task;

class TaskRepository implements TaskRepositoryInterface
{
    use Base;

    protected $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }
    
    public function read(): JsonResponse
    {
        try {
            $tasks = $this->model->query()->orderBy('id')->paginate(20);
            return $this->handleResponse($tasks);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function create($model): JsonResponse
    {
        try {
            $task = $this->model->query()->create($model);
            return $this->handleResponse($task);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function readById($id)
    {
        try{
            $task = $this->model->query()->find($id);;
            return $this->handleResponse($task);
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
