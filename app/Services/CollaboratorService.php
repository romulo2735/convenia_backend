<?php

namespace App\Services;

use App\Http\Requests\ImportFileRequest;
use App\Jobs\ImportCollaboratorsJob;
use App\Models\Collaborator;
use Illuminate\Support\Facades\Auth;

class CollaboratorService
{
    public function list(array $filters = [])
    {
        $query = Collaborator::query()->where('user_id', Auth::id());

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowed = ['name', 'email', 'cpf',  'created_at'];

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    public function create(array $data): Collaborator
    {
        return Collaborator::create($data);
    }

    public function find(Collaborator $collaborator): Collaborator
    {
        return Collaborator::where('user_id', Auth::id())->findOrFail($collaborator);
    }

    public function update(Collaborator $collaborator, array $data): Collaborator
    {
        $collaborator->update($data);
        return $collaborator;
    }

    public function delete(Collaborator $collaborator): void
    {
        $collaborator->delete();
    }

    public function import(ImportFileRequest $request): array
    {
        $path = $request->file('file')->store('imports');
        $user = $request->user();

        ImportCollaboratorsJob::dispatch($path, $user);

        return [
            'success' => true,
            'message' => 'Arquivo recebido e importação iniciada em background.',
        ];
    }
}
