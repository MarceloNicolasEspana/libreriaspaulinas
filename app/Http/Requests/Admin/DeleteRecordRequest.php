<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-admin') ?? false;
    }

    public function rules(): array
    {
        return ['confirmed' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return ['confirmed.required' => 'Debes confirmar esta acción.', 'confirmed.accepted' => 'Debes confirmar esta acción.'];
    }
}
