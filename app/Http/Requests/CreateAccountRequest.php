<?php

namespace App\Http\Requests;

use App\Traits\ResponseAPI;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAccountRequest extends FormRequest
{
    use ResponseAPI;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:25',
            'email' => 'required|email',
            'user_name' => 'required|string|min:3|max:25|unique:users',
            'password' => 'required|string|min:3|confirmed',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = $this->errorResponse($validator->errors(), 422);
        throw new HttpResponseException($response);
    }
}
