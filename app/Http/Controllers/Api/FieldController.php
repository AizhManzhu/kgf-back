<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ButtonRequest;
use App\Http\Requests\FieldRequest;
use App\Models\Button;
use App\Models\Field;
use App\Repository\EventRepositoryInterface;
use App\Repository\FieldRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;

class FieldController extends Controller
{
    private $field;

    public function __construct(FieldRepositoryInterface $field)
    {
        $this->field = $field;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->field->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FieldRequest $request
     * @return JsonResponse
     */
    public function store(FieldRequest $request): JsonResponse
    {
        return $this->field->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->field->readById($id);
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FieldRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(FieldRequest $request, int $id): JsonResponse
    {
        return $this->field->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->field->delete($id);
    }

    public function addButton(ButtonRequest $request): JsonResponse
    {
        try {
            $button = Button::create($request->all());
            return $this->handleResponse($button);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function deleteButton(Request $request): JsonResponse
    {
        try {
            $result = Button::query()->where('id', $request->id)->update(['field_id' => null]);
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function getCurrentEventFilterList(EventRepositoryInterface $eventRepository): JsonResponse
    {
        $eventId = ($eventRepository->getCurrentEvent())->id;
        $field = Field::with('buttons')
            ->where('event_id', $eventId)
            ->where('type', '=', 'button')
            ->where('is_base', '=', 0)->get(['id', 'name']);
        return $this->handleResponse($field);
    }

    public function getBaseFields(FieldRepositoryInterface $fieldRepository): JsonResponse
    {
        $fields = $fieldRepository->getBaseFields();
        return $this->handleResponse($fields);
    }

    public function saveBaseFields(FieldRepositoryInterface $fieldRepository, Request $request): JsonResponse
    {
        try {
            $fieldRepository->saveBaseFields($request->only(['first_name', 'last_name', 'phone', 'email', 'company', 'position']));
            return $this->handleResponse(1);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function getFields(EventRepositoryInterface $eventRepository) : JsonResponse
    {   
        $eventId = ($eventRepository->getCurrentEvent())->id;
        $fields = [];
        foreach (Field::with('buttons')->get() as $field) {
            if($field->is_base === 1 || $field->event_id === $eventId){
                array_push($fields, $field);
            }
        }
        return $this->handleResponse($fields);
    }
}
