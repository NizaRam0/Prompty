<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2',
            'content' => 'required|string',
            // 'tags' => 'array',
            // 'tags.*' => 'string|min:2'
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.min' => 'The title must be at least :min characters.',
            'content.required' => 'The content field is required.',
            'content.string' => 'The content must be a string.',
        //     'tags.array' => 'The tags must be an array.',
        //     'tags.*.string' => 'Each tag must be a string.',
        //     'tags.*.min' => 'Each tag must be at least :min characters.',
        ];
    }

}
