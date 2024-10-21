<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class EventRequest extends FormRequest
{
    protected $required = 'обязательное поле для заполнения';
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'description' => 'nullable',
            'address' => 'nullable',
            'event_date' => 'nullable',
            'is_current' => 'required',
            'welcome_message' => 'nullable',
            'thank_you_message' => 'nullable',
        ];

    }

    public function messages()
    {
        return [
            'title.required' => "Название: $this->required",
        ];
    }
}
