<?php

namespace App\Policies;

use App\Models\ScanResult;
use App\Models\User;

class ScanResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('scans.viewAny');
    }

    public function view(User $user, ScanResult $scanResult): bool
    {
        return $user->can('scans.view');
    }

    public function create(User $user): bool
    {
        return $user->can('scans.create');
    }

    public function delete(User $user, ScanResult $scanResult): bool
    {
        return $user->can('scans.delete');
    }
}
