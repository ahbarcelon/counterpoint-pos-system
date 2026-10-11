<?php

namespace App\Services;

final class StaffAccess
{
    public static function canViewOrEdit(bool $isAdmin, int $viewerId, int $targetId): bool
    {
        return $isAdmin || ($viewerId > 0 && $viewerId === $targetId);
    }

    public static function canCreateOrDelete(bool $isAdmin): bool
    {
        return $isAdmin;
    }
}
