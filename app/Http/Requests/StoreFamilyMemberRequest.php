<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFamilyMemberRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'anniversary_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'hobbies' => ['sometimes', 'array'],
            'hobbies.*' => ['string', 'max:100'],
            'favorite_sports' => ['sometimes', 'array'],
            'favorite_sports.*' => ['string', 'max:100'],
            'favorite_music' => ['sometimes', 'array'],
            'favorite_music.*' => ['string', 'max:100'],
            'favorite_food' => ['sometimes', 'array'],
            'favorite_food.*' => ['string', 'max:100'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpeg,png,webp,gif,avif', 'max:10240'],
        ];
    }
}
