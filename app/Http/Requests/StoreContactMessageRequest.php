<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+() .-]+$/'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ingresa tu nombre.',
            'name.max' => 'El nombre no puede superar los 120 caracteres.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 255 caracteres.',
            'phone.max' => 'El teléfono no puede superar los 30 caracteres.',
            'phone.regex' => 'Ingresa un teléfono válido.',
            'subject.required' => 'Ingresa el asunto de tu consulta.',
            'subject.max' => 'El asunto no puede superar los 160 caracteres.',
            'message.required' => 'Escribe tu mensaje.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'message.max' => 'El mensaje no puede superar los 5000 caracteres.',
            'website.prohibited' => 'No pudimos procesar el formulario. Inténtalo nuevamente.',
        ];
    }
}
