<?php

namespace App\Repository;

use App\Models\Event;
use App\Models\Member;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MemberRepository implements MemberRepositoryInterface
{

    protected $model;

    public function __construct(Member $model)
    {
        $this->model = $model;
    }

    public function read()
    {
        return $this->model->query()->orderByDesc('id')->paginate(20);
    }

    public function create($model)
    {
        return $this->model->query()->create($model);
    }

    public function readById($id)
    {
        return $this->model->query()->find($id);
    }

    public function update($id, $array)
    {
        return $this->model->query()->where('id', $id)->update($array);
    }

    public function delete($id)
    {
        return $this->model->query()->with('fieldValues')->with('eventMembers')->find($id)->delete();
    }

    public function getByName(string $name)
    {
        $eventId = ((new EventRepository(new Event()))->getCurrentEvent())->id;
        return $this->model->query()->with(['eventMembers' => function($query) use ($eventId) {
            $query->where('is_activated', 1)
                ->where('event_id', $eventId);
        }])->where(function ($query) use ($name){
            $query->where('first_name', 'LIKE', "%$name%")
                ->orWhere('last_name', 'LIKE', "%$name%");
        })->get();
    }

    public function getByTelegramId(string $telegramId, bool $withBaseFields = false)
    {
        $model = $this->model->query();
        return $model->with('telegramRequestLog')
            ->with('events')
            ->whereTelegramId($telegramId)->first();
    }

    public function isRegistered($member)
    {
        if (!$member) {
            return false;
        }
        return true;
    }

    public function getByEmail(string $email, string $lastName)
    {
        return $this->model->query()->select($this->model->selects)->where('last_name', $lastName)->where('email', $email)->orderByDesc('id')->first();
    }

    public function getByAttributes(array $selects, array $keyValues) {
        $query = $this->model->query()->select($selects);
        foreach ($keyValues as $key => $val) {
            if ($key == 'phone') {
                $val = trim($val, ' +()-');
            }
            $query->where($key, $val);
        }
        return $query->orderByDesc('id')
            ->first();
    }

    public function updateOrCreate($attributes, $values): Model
    {
        return $this->model->query()->updateOrCreate($attributes, $values);
    }

    public function getByEventMemberId($id)
    {
        return $this->model->query()->whereHas('eventMembers', function ($query) use ($id){
            return $query->find($id);
        });
    }
}
