<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DocumentUploadRequest extends FormRequest
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
            'documents' => 'required|array', // Ensure 'documents' is an array
            'documents.*' => 'required|file|mimes:doc,docx,txt,pdf|max:2048', // Validate each file in the array
        ];
    }

    /*
     * Handle failed validation for API responses.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        // Dump the entire request data (use only for debugging)
        dd($this->all());

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
