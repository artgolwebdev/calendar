<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class StoreCalendarWizardRequest extends FormRequest
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
            'cover_image_path' => 'nullable|image|max:51200',
            'members' => ['nullable', 'array', 'max:20'],
            'members.*.name' => ['required_with:members', 'string', 'max:255'],
            'members.*.birth_date' => ['required_with:members', 'date'],
            'members.*.hobbies' => ['nullable', 'array'],
            'members.*.hobbies.*' => ['string', 'max:100'],
            'members.*.favorite_sports' => ['nullable', 'array'],
            'members.*.favorite_sports.*' => ['string', 'max:100'],
            'members.*.favorite_music' => ['nullable', 'array'],
            'members.*.favorite_music.*' => ['string', 'max:100'],
            'members.*.favorite_food' => ['nullable', 'array'],
            'members.*.favorite_food.*' => ['string', 'max:100'],
            'members.*.image' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif,avif', 'max:10240'],
        ];
    }

    /**
     * The wizard submits over XHR and the app only renders exceptions as JSON
     * for api/* routes, so force a 422 JSON response with the error messages.
     */
    protected function failedValidation(Validator $validator): void
    {
        $exception = new ValidationException($validator);

        throw new HttpResponseException(
            new JsonResponse([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
