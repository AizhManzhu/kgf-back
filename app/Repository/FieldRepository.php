<?php

namespace App\Repository;

use App\Models\Event;
use App\Models\Field;
use App\Models\FieldValues;
use App\Models\Member;
use Exception;
use Illuminate\Http\JsonResponse;

class FieldRepository implements FieldRepositoryInterface
{
    use Base;

    protected $model;

    public function __construct(Field $model)
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
            if ($model['is_base'] == Field::IS_NOT_BASE) {
                $model['event_id'] = ((new EventRepository(new Event()))->getCurrentEvent())->id;
            }
            $event = $this->model->query()->create($model);
            return $this->handleResponse($event);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function readById($id)
    {
        return $this->model->query()->with('buttons')->find($id);
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

    public function getValues(string $telegramId, EventRepositoryInterface $eventRepository, int $isBase = Field::IS_BASE)
    {
        $event = $eventRepository->getCurrentEvent();
        $fieldValuesId = FieldValues::query()
            ->where('telegram_id', $telegramId)
            ->pluck('field_id');
        $query = Field::query()->with('buttons')->whereNotIn('id', $fieldValuesId);
        if ($isBase == Field::IS_BASE) {
            $query = $query->where('is_base', Field::IS_BASE);
        } else {
            $query = $query->where('is_base', Field::IS_NOT_BASE)
                ->where('event_id', $event->id);
        }
        $result = $query->first();
        return $result;
    }

    public function createMember($telegramId)
    {
        $fieldValues = FieldValues::query()->with('field')->where('telegram_id', $telegramId)->get();
        $memberData = [
            'telegram_id' => $telegramId
        ];
        foreach ($fieldValues as $value)
        {
            if (isset($value->field) && $value->field->is_base == Field::IS_BASE)
            {
                $memberData[$value->field->member_field] = $value->value;
            }
        }
        return Member::query()->updateOrCreate($memberData);
    }

    public function getBaseFields()
    {
        return Field::query()->where('is_base', Field::IS_BASE)->get(['member_field', 'text']);
    }

    public function saveBaseFields(array $array): void
    {
        foreach ($array as $key => $value){
            Field::query()->where('member_field', $key)->update(['text'=>$value]);
        }
    }
}
