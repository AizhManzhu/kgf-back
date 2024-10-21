<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ButtonRequest extends FormRequest
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
        $rules = ['text' => 'required'];
        if (strpos($this->getUri(), 'fields')) {
            $rules['field_id'] = 'required';
        }
        return $rules;

    }

    public function messages()
    {
        return [
            'text.required' => "Текст сообщения: $this->required",
            'field_id.required' => $this->required,
//            'preparedtext_id.required' => "Текст сообщения: $this->required",
        ];
    }
}
