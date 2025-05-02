<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:255|confirmed',
        ];
    }

    /*
     * Handle failed validation for API responses.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        if ($validator->header('Accept') === 'application/json') {
            // Return JSON response for API requests
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }
        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator) // Pass validation errors
                ->withInput() // Retain old input values
        );
    }
}
