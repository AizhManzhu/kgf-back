<?php
namespace App\Repository;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class UserRepository implements UserRepositoryInterface {
    use Base;

    protected $model;

    public function __construct(User $model)
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

    public function create($model)
    {
         return $this->model->query()->create($model);
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

    public function update($id, $array)
    {
        return $this->model->query()->where('id', $id)->update($array);
    }

    /**
     * @deprecated
     */
    public function updatePartData($id, $array): JsonResponse
    {
        try{
            $user = User::find($id);
            $user->name = $array['name'];
            $user->email = $array['email'];
            $user->save();
            return $this->handleResponse(null);
        } catch(Exception $e) {
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
