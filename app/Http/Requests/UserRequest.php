<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected $rules = [
        'name' => 'required',
        'email' => 'required|email',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch($this->getMethod()){
            case "POST":
                return $this->rules;
            case "PUT":
                return ['name' =>'required', 'email' => 'required|email', 'role' => 'string'];
        }
    }

    public function messages()
    {
        return [
            'name.required' => "Укажите имя",
            'email.required' => "Укажите email",
        ];
    }
}
