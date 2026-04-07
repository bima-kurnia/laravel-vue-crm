<?php

namespace App\Http\Requests\Deal;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
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
            'customer_id'         => ['required', 'uuid'],
            'owner_id'            => ['required', 'uuid'],
            'title'               => ['required', 'string', 'max:255'],
            'value'               => ['sometimes', 'numeric', 'min:0'],
            'currency'            => ['sometimes', 'string', 'size:3'],
            'status'              => ['sometimes', 'string', 'in:open,won,lost,stalled'],
            'stage'               => ['sometimes', 'string', 'in:prospecting,qualification,proposal,negotiation,closed'],
            'expected_close_date' => ['sometimes', 'nullable', 'date'],
            'custom_data'         => ['sometimes', 'array'],
        ];
    }
}
