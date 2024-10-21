<?php

namespace App\Repository;

use App\Models\Speaker;
use Exception;
use Illuminate\Http\JsonResponse;

class SpeakerRepository implements SpeakerRepositoryInterface
{
    use Base;

    protected $model;

    public function __construct(Speaker $model)
    {
        $this->model = $model;
    }

    public function read()
    {
        return $this->model->query()->orderByDesc('id')->paginate(20);
    }

    public function create($model)
    {
        if ($model['is_current']) {
            $this->model->where('event_id', $model['event_id'])->where('is_current', 1)->update(['is_current'=>0]);
        }
        return $this->model->query()->updateOrCreate([
            'event_id' => $model['event_id'],
            'start_at'=>$model['start_at']
        ], $model);
    }

    public function readById($id): JsonResponse
    {
        try {
            $model = $this->model->query()->find($id);
            return $this->handleResponse($model);
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

    public function makeCurrent($id, $eventId)
    {
        $this->model->where('event_id', $eventId)->where('is_current', 1)->update(['is_current' => 0]);
        $this->model->where('event_id', $eventId)->where('id', $id)->update(['is_current' => 1]);
    }

    public function getCurrentEventSpeakers($eventId)
    {
        return $this->model->where('event_id', $eventId)->orderBy('start_at')->get();
    }
}
