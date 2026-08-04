<?php

namespace App\Policies;

use App\Models\Keyword;
use App\Models\User;

class KeywordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('keywords.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('keywords.create');
    }

    public function update(User $user, Keyword $keyword): bool
    {
        return $user->can('keywords.update');
    }

    public function delete(User $user, Keyword $keyword): bool
    {
        return $user->can('keywords.delete');
    }
}
