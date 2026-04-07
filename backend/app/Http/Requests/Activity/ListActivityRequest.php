<?php

namespace App\Http\Requests\Activity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListActivityRequest extends FormRequest
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
            'subject_type' => ['sometimes', 'string', 'in:customer,deal'],
            'subject_id'   => ['sometimes', 'uuid', 'required_with:subject_type'],
            'user_id'      => ['sometimes', 'uuid'],
            'event'        => ['sometimes', 'string', 'max:100'],
            'events'       => ['sometimes', 'array'],
            'events.*'     => ['string', 'max:100'],
            'date_from'    => ['sometimes', 'date'],
            'date_to'      => ['sometimes', 'date', 'after_or_equal:date_from'],
            'with_subject' => ['sometimes', 'boolean'],
            'per_page'     => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
