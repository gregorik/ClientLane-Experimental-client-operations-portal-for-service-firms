<?php

namespace App\Http\Requests;

use App\Enums\RequestPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $isStaff = $this->user()?->isStaff() ?? false;

        return [
            'client_id' => $isStaff
                ? ['required', 'integer', 'exists:clients,id']
                : ['nullable'],
            'title' => ['required', 'string', 'max:140'],
            'request_type' => ['required', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::enum(RequestPriority::class)],
            'due_at' => ['nullable', 'date'],
            'assigned_to_user_id' => $isStaff
                ? ['nullable', 'integer', 'exists:users,id']
                : ['prohibited'],
        ];
    }
}
