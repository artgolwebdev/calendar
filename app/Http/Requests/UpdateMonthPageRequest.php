<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMonthPageRequest extends FormRequest
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
            'font_choice' => 'sometimes|nullable|string|in:default,modern,traditional,elegant',
            'background_image_path' => 'nullable|string|max:255',
            'custom_image_path' => 'nullable|image|max:51200', // Max 50MB
            'overlay_opacity' => 'sometimes|nullable|integer|min:0|max:100',
            'day_box_bg_color' => 'sometimes|nullable|string|max:7',
            'day_box_font_color' => 'sometimes|nullable|string|max:7',
            'day_box_bg_opacity' => 'sometimes|nullable|integer|min:0|max:100',
            'show_adjacent_month_days' => 'sometimes|nullable|boolean',
        ];
    }
}
