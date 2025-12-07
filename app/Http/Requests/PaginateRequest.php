<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginateRequest extends FormRequest
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
            'page' => ['nullable' , 'integer' , 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100']
        ];
    }

    public function getPage(): int
    {
        return (int) ($this->input('page' , 1));
    }

    public function getPerPage(): int
    {
        return (int) ($this->input('per_page' , 10));
    }
}
