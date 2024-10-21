<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldRequest extends FormRequest
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
            'text' => 'required',
            'type' => 'required',
            'is_base' => 'in:0,1',
            'name' => 'required',
            'member_field' => Rule::requiredIf($this->is_base==1),
        ];

    }

    public function messages()
    {
        return [
            'name.required' => "Укажите название",
            'text.required' => "Текст сообщения: $this->required",
            'type.required' => "Тип: $this->required",
            'is_base.in' => "Укажите корректное значение [0, 1]",
            'member_field.required' => "Укажите поля на которую должен ссылаться данные"
        ];
    }
}
