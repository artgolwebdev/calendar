<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
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
            'members.*.key' => ['nullable', 'string', 'max:40'],
            'members.*.name' => ['required_with:members', 'string', 'max:255'],
            'members.*.birth_date' => ['required_with:members', 'date'],
            'members.*.anniversary_date' => ['nullable', 'date'],
            'members.*.hobbies' => ['nullable', 'array'],
            'members.*.hobbies.*' => ['string', 'max:100'],
            'members.*.favorite_sports' => ['nullable', 'array'],
            'members.*.favorite_sports.*' => ['string', 'max:100'],
            'members.*.favorite_music' => ['nullable', 'array'],
            'members.*.favorite_music.*' => ['string', 'max:100'],
            'members.*.favorite_food' => ['nullable', 'array'],
            'members.*.favorite_food.*' => ['string', 'max:100'],
            'members.*.image' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif,avif', 'max:10240'],
            'events' => ['nullable', 'array', 'max:50'],
            'events.*.title' => ['required_with:events', 'string', 'max:255'],
            'events.*.description' => ['nullable', 'string', 'max:1000'],
            'events.*.event_date' => ['required_with:events', 'date'],
            'events.*.start_time' => ['nullable', 'date_format:H:i'],
            'events.*.end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'events.*.family_member_key' => ['nullable', 'string', Rule::in($this->memberKeys())],
            'events.*.cover_image_path' => ['nullable', 'image', 'max:51200'],
            'theme' => ['nullable', 'string', Rule::in(array_keys(config('themes')))],
        ];
    }

    /**
     * The temporary client keys of the family members submitted in this
     * request, used to link manual events to members created in the same
     * submission (before they have database ids).
     *
     * @return array<int, string>
     */
    private function memberKeys(): array
    {
        return collect($this->input('members', []))
            ->pluck('key')
            ->filter()
            ->values()
            ->all();
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
