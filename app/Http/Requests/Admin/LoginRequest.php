<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email', 'max:255'], 'password' => ['required', 'string', 'max:1024'], 'remember' => ['sometimes', 'boolean']];
    }

    public function authenticate(): void
    {
        $key = Str::lower($this->string('email')->toString()).'|'.$this->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Demasiados intentos. Intenta nuevamente en un minuto.']);
        }
        if (! Auth::attempt([...$this->safe()->only(['email', 'password']), 'is_admin' => true], $this->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Las credenciales no permiten acceder al panel.']);
        }
        RateLimiter::clear($key);
    }

    public function messages(): array
    {
        return ['required' => 'Este campo es obligatorio.', 'email.email' => 'Escribe un correo válido.'];
    }
}
