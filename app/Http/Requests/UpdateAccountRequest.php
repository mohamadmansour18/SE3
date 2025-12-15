<?php

namespace App\Http\Requests;

use App\Exceptions\ApiException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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
            'account_id'  => 'required|integer|exists:accounts,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'status'      => 'sometimes|string|in:نشط,مجمد,موقوف,مغلق',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fields = ['name', 'description', 'status'];

            $hasAny = collect($fields)->some(function ($field) {
                return $this->has($field);
            });

            if (!$hasAny) {
                throw new ApiException("يجب تعديل حقل واحد على الاقل من الحقول المذكورة" ,422);
            }
        });
    }

    public function getAccountId(): int
    {
        return (int) $this->input('account_id');
    }


    public function getUpdateData(): array
    {
        return $this->only(['name', 'description', 'status']);
    }
}
