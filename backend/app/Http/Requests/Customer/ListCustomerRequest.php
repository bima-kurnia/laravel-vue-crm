<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Convert only_trashed=true from URL query
            'only_trashed' => filter_var($this->only_trashed, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'        => ['sometimes', 'string', 'max:100'],
            'status'        => ['sometimes', 'string', 'in:active,inactive,lead'],
            'company'       => ['sometimes', 'string', 'max:100'],
            'sort_by'       => ['sometimes', 'string', 'in:name,email,company,created_at,updated_at'],
            'sort_dir'      => ['sometimes', 'string', 'in:asc,desc'],
            'per_page'      => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'          => ['sometimes', 'integer', 'min:1'],
            'only_trashed'  => ['sometimes', 'boolean'],
        ];
    }
}
