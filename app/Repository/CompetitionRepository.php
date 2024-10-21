<?php
namespace App\Repository;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Competition;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    use Base;

    protected $model;

    public function __construct(Competition $model)
    {
        $this->model = $model;
    }
    
    public function read(): JsonResponse
    {
        try {
            $competitions = $this->model->query()->orderBy('id')->paginate(20);
            return $this->handleResponse($competitions);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function create($model): JsonResponse
    {
        try {
            $competition = $this->model->query()->create($model);
            return $this->handleResponse($competition);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function readById($id)
    {
        try{
            $competition = $this->model->query()->with('tasks')->find($id);;
            return $this->handleResponse($competition);
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
