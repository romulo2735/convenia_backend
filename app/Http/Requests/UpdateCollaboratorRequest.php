<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCollaboratorRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->route('collaborator')?->id;

        return [
            'name'  => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:collaborators,email,{$id}",
            'cpf'   => "sometimes|string|min:11|max:14|unique:collaborators,cpf,{$id}",
            'city'  => 'sometimes|string|max:100',
            'state' => 'sometimes|string|size:2',
        ];
    }
}
