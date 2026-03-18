<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }
}
