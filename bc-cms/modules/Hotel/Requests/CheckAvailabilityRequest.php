<?php

namespace Modules\Hotel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id'   => ['required', 'integer', 'exists:bc_hotels,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date'   => ['required', 'date_format:Y-m-d'],
            'adults'     => ['required', 'integer', 'min:1'],
            'children'   => ['required', 'integer', 'min:0'],
            'firstLoad'  => ['required', 'boolean'],
        ];
    }
}
