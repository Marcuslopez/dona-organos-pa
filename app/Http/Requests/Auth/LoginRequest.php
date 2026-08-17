<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
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

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt([
            'email' => $this->string('email')->toString(),
            'password' => $this->string('password')->toString(),
            'is_active' => true,
        ])) {
            RateLimiter::hit($this->throttleKey(), 60);

            if (RateLimiter::attempts($this->throttleKey()) >= 3) {
                $this->throwRateLimitedException();
            }

            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no son correctas.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        event(new Lockout($this));

        $this->throwRateLimitedException();
    }

    private function throwRateLimitedException(): never
    {
        $seconds = RateLimiter::availableIn($this->throttleKey());
        $this->session()->flash('login_retry_after', $seconds);

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Intenta nuevamente en {$seconds} segundos.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
