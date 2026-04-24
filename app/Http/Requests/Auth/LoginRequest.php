<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $email = (string) $this->input('email', '');
        $email = preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $email);
        $password = (string) $this->input('password', '');
        $password = preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $password);
        $this->merge([
            'email' => strtolower(trim($email)),
            'password' => $password,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Validate the request's credentials and return the user without logging them in.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateCredentials(): User
    {
        $this->ensureIsNotRateLimited();

        /** @var User $user */
        $user = Auth::getProvider()->retrieveByCredentials($this->only('email', 'password'));
        $originalPassword = (string) $this->input('password');
        $trimmedPassword = trim($originalPassword);

        $valid = $user && Auth::getProvider()->validateCredentials($user, ['password' => $originalPassword]);

        if (! $valid && $user && $trimmedPassword !== $originalPassword) {
            $valid = Auth::getProvider()->validateCredentials($user, ['password' => $trimmedPassword]);
            if ($valid) {
                $this->merge(['password' => $trimmedPassword]);
                Log::warning('Login password validated after trimming whitespace', [
                    'email' => strtolower(trim((string) $this->input('email'))),
                    'ip' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                ]);
            }
        }

        if (! $valid) {
            Log::warning('Login failed: credentials mismatch', [
                'email' => strtolower(trim((string) $this->input('email'))),
                'user_found' => (bool) $user,
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'password_length' => strlen((string) $this->input('password')),
                'password_has_space' => (bool) preg_match('/\s/', (string) $this->input('password')),
                'password_has_nonprintable' => (bool) preg_match('/[\x00-\x1F\x7F]/', (string) $this->input('password')),
            ]);
            @error_log(json_encode([
                'event' => 'login_failed_credentials_mismatch',
                'email' => strtolower(trim((string) $this->input('email'))),
                'user_found' => (bool) $user,
                'ip' => $this->ip(),
                'ua' => $this->userAgent(),
            ]));
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate-limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return $this->string('email')
            ->lower()
            ->append('|'.$this->ip())
            ->transliterate()
            ->value();
    }
}
