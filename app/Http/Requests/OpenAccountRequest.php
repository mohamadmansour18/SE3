<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenAccountRequest extends FormRequest
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
            'name' => ['required' , 'string' , 'max:255'],
            'description' => ['required' , 'string' , 'max:512'],
            'account_type'   => ['required' , 'string' , 'in:جاري,توفير,قرض,استثماري'],
            'initial_amount' => 'required|numeric',
        ];
    }
}
