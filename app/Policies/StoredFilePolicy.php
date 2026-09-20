<?php

namespace App\Policies;

use App\Models\StoredFile;
use App\Models\User;

class StoredFilePolicy
{
    /**
     * Determine whether the system user can view any files.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the system user can view/download the file.
     */
    public function view(User $user, StoredFile $storedFile): bool
    {
        return $user->id === $storedFile->user_id;
    }

    /**
     * Determine whether the system user can update the file status/metadata.
     */
    public function update(User $user, StoredFile $storedFile): bool
    {
        return $user->id === $storedFile->user_id;
    }

    /**
     * Determine whether the system user can physically delete the file.
     */
    public function delete(User $user, StoredFile $storedFile): bool
    {
        return $user->id === $storedFile->user_id;
    }
}
