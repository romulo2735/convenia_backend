<?php

namespace App\Observers;

use App\Models\Collaborator;
use Illuminate\Support\Facades\Auth;

class CollaboratorObserver
{
    /**
     * Handle the Collaborator "created" event.
     */
    public function creating(Collaborator $collaborator): void
    {
        if (Auth::check() && !$collaborator->user_id) {
            $collaborator->user_id = Auth::id();
        }
    }
}
