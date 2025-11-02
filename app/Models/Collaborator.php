<?php

namespace App\Models;

use Database\Factories\CollaboratorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Collaborator extends Model
{
    /** @use HasFactory<CollaboratorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'city',
        'state',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        $this->belongsTo(User::class);
    }

    /**
     * Scope to retrieve collaborators only from the authenticated user
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        $model = $this->where($field, $value)->where('user_id', auth()->id())->first();

        if (!$model) {
            abort(404, 'Collaborator not found or does not belong to the authenticated user.');
        }

        return $model;
    }
}
