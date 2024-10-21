<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SpeakerRequest extends FormRequest
{
    protected string $required = 'обязательное поле для заполнения';
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
            'speaker_image' => 'required',
            'event_id' => 'required',
            'start_at' => 'required',
            'description' => 'nullable',
            'is_current' => 'nullable'
        ];

    }

    public function messages()
    {
        return [
            'event_id.required' => "ID мероприятия: $this->required",
            'speaker_image.required' => "Картинка: $this->required",
            'start_at.required' => "Время начало: $this->required",
        ];
    }
}
