<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;

class TicketActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_no' => ['required', 'string', 'max:20', 'exists:queue_tickets,ticket_no'],
        ];
    }
}
