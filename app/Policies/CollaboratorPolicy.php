<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Collaborator;
use Illuminate\Auth\Access\Response;

class CollaboratorPolicy
{
    /**
     * Determina se o usuário pode visualizar o colaborador.
     */
    public function view(User $user, Collaborator $collaborator): Response
    {
        return $user->id === $collaborator->user_id
            ? Response::allow()
            : Response::deny('You do not have permission to view this collaborator.');
    }

    /**
     * Determina se o usuário pode atualizar o colaborador.
     */
    public function update(User $user, Collaborator $collaborator): Response
    {
        return $user->id === $collaborator->user_id
            ? Response::allow()
            : Response::deny('You do not have permission to edit this contributor.');
    }

    /**
     * Determina se o usuário pode excluir o colaborador.
     */
    public function delete(User $user, Collaborator $collaborator): Response
    {
        return $user->id === $collaborator->user_id
            ? Response::allow()
            : Response::deny('You do not have permission to delete this collaborator.');
    }
}
