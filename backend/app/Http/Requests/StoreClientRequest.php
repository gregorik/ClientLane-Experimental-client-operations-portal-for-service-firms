<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:120'],
            'primary_contact_name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                Rule::unique('clients', 'email')->where(
                    fn ($query) => $query->where('firm_id', $this->user()?->firm_id)
                ),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'create_portal_user' => ['nullable', 'boolean'],
            'portal_user_name' => ['nullable', 'string', 'max:120'],
            'portal_user_title' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }
}
