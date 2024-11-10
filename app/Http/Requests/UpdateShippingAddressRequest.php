<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateShippingAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'max:50',
            'shipping_name' => 'string',
            'shipping_phone' => 'string|numeric|min_digits:10|max_digits:15',
            'shipping_email' => 'string|email',
            'shipping_address' => 'string|max:150',
            'shipping_province_id' => 'integer|max_digits:2',
            'shipping_city_id' => 'integer|max_digits:3',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response(['errors' => $validator->getMessageBag()], 401));
    }
}
