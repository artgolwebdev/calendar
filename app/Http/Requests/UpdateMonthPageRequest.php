<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Normalize the checkbox value before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('show_adjacent_month_days')) {
            $this->merge([
                'show_adjacent_month_days' => $this->boolean('show_adjacent_month_days'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'font_choice' => ['sometimes', 'nullable', Rule::in(['default', 'modern', 'traditional', 'elegant'])],
            'overlay_opacity' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'day_box_bg_color' => ['sometimes', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'day_box_font_color' => ['sometimes', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'day_box_bg_opacity' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'weekday_color' => ['sometimes', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'show_adjacent_month_days' => ['sometimes', 'nullable', 'boolean'],
            'custom_image_path' => ['sometimes', 'nullable', 'image', 'max:10240'],
            'background_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
        ];
    }
}
