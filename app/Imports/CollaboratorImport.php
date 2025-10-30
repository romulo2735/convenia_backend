<?php

namespace App\Imports;

use App\Models\Collaborator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CollaboratorImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        return new Collaborator([
            'name' => $row['name'] ?? null,
            'email' => $row['email'] ?? null,
            'cpf' => $row['cpf'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'user_id' => $this->userId,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string|max:255',
            '*.email' => 'required|email|unique:collaborators,email',
            '*.cpf' => 'required|string|min:11|max:14|unique:collaborators,cpf',
            '*.city' => 'nullable|string|max:100',
            '*.state' => 'nullable|string|size:2',
        ];
    }
}
