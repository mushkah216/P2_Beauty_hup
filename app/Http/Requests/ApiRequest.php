<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $firstMessage = collect($errors)->flatten()->first() ?? 'Validation failed.';

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstMessage,
            'errors' => (object) $errors,
        ], 422));
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Unauthorized.',
        ], 403));
    }
}