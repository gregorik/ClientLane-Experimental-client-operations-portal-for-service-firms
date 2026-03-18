<?php

namespace App\Http\Requests;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:140'],
            'request_type' => ['sometimes', 'string', 'max:80'],
            'summary' => ['sometimes', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(RequestStatus::class)],
            'priority' => ['sometimes', Rule::enum(RequestPriority::class)],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'assigned_to_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }
}
