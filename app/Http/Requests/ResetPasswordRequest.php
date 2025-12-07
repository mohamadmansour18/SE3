<?php

namespace App\Http\Requests;

use App\Rules\AllowedEmailDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as password_rule;

class ResetPasswordRequest extends FormRequest
{
    protected $stopOnFirstFailure = true ;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required' , 'email' , 'exists:users,email' , new AllowedEmailDomain()] ,
            'password' => ['required' , 'confirmed' , password_rule::min(6)->numbers()->letters()]
        ];
    }
}
