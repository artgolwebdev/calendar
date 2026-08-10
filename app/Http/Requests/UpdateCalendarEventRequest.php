<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalendarEventRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'event_date' => 'sometimes|required|date',
            'event_type' => 'sometimes|required|in:custom',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'family_member_id' => [
                'nullable',
                Rule::exists('family_members', 'id')->where('calendar_id', $this->route('calendar')->id),
            ],
            'cover_image_path' => 'nullable|image|max:51200',
        ];
    }
}
