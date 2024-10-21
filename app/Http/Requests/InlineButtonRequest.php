<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class InlineButtonRequest extends FormRequest
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
            'name' => 'required',
            'text' => 'required',
            'row' => 'nullable',
            'callback_data' => 'required',
            'auto_answer' => 'nullable',
            'event_id' => 'nullable'
        ];

    }

    public function messages()
    {
        return [
            'name.required' => "Название: $this->required",
            'text.required' => "Название: $this->required",
            'callback_data.required' => "Возвращаемое значение: $this->required",
        ];
    }

    // match ($this->getMethod()) {
    //            'POST' => $rules,
    //            'PUT' => $rules,
    //            'DELETE' => [
    //                'game_id' => 'required|integer|exists:games,id'
    //            ],
    //        };
}
