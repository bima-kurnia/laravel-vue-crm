<?php

namespace App\Http\Requests\Deal;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListDealRequest extends FormRequest
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
            'search'              => ['sometimes', 'string', 'max:100'],
            'status'              => ['sometimes', 'string', 'in:open,won,lost,stalled'],
            'stage'               => ['sometimes', 'string', 'in:prospecting,qualification,proposal,negotiation,closed'],
            'customer_id'         => ['sometimes', 'uuid'],
            'owner_id'            => ['sometimes', 'uuid'],
            'currency'            => ['sometimes', 'string', 'size:3'],
            'value_min'           => ['sometimes', 'numeric', 'min:0'],
            'value_max'           => ['sometimes', 'numeric', 'min:0', 'gte:value_min'],
            'close_date_from'     => ['sometimes', 'date'],
            'close_date_to'       => ['sometimes', 'date', 'after_or_equal:close_date_from'],
            'sort_by'             => ['sometimes', 'string', 'in:title,value,stage,status,expected_close_date,created_at'],
            'sort_dir'            => ['sometimes', 'string', 'in:asc,desc'],
            'per_page'            => ['sometimes', 'integer', 'min:1', 'max:100'],
            'with_trashed'        => ['sometimes', 'boolean'],
        ];
    }
}
