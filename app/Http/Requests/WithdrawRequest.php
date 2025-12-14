<?php

namespace App\Http\Requests;

use App\Rules\ArabicOnly;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
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
            'account_id' => ['required' , 'integer' , 'exists:accounts,id'] ,
            'name' => ['required' , 'string' , 'max:255' , new ArabicOnly()] ,
            'amount'     => ['required' , 'numeric' , 'min:1'] ,
        ];
    }
}
