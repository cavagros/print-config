<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PrintConfiguration;

class PrintConfigurationPolicy
{
    public function view(User $user, PrintConfiguration $configuration)
    {
        return true;
    }

    public function update(User $user, PrintConfiguration $configuration)
    {
        return true;
    }

    public function delete(User $user, PrintConfiguration $configuration)
    {
        return true;
    }

    public function adminAccess(User $user)
    {
        return $user->is_admin;
    }
} 