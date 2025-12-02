<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebHookPaymentRequest extends FormRequest
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
            "idempotency_key" => "required|string",
            "order_id" => "required",
            "status" => "required|string|in:success,failure",
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return errorJsonResponse(
            $validator->errors()->first(),
            $validator->errors(),
        );
    }
}