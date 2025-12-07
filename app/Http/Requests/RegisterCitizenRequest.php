<?php

namespace App\Http\Requests;

use App\Rules\AllowedEmailDomain;
use App\Rules\ArabicOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as password_rule;

class RegisterCitizenRequest extends FormRequest
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
            'name' => ['required', new ArabicOnly() , 'max:255'],
            'email' => ['required' , 'email' , 'string' , 'unique:users,email' , 'max:255' , new AllowedEmailDomain()],
            'password' => ['required' , password_rule::min(6)->numbers()->letters()],
            'national_number' => ['required' , 'digits:11' , 'unique:citizen_profiles,national_number' , 'max:11'],
        ];
    }
}
