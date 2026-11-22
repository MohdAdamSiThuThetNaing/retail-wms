<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WmsNotification;
use Illuminate\Database\Eloquent\Builder;

class NotificationService
{
    public function notifyUser(
        User $user,
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        array $metadata = [],
    ): void {
        if (!$user->is_active) {
            return;
        }

        $user->notify(
            new WmsNotification(
                $category,
                $title,
                $message,
                $url,
                $metadata
            )
        );
    }

    public function notifyUsers(
        iterable $users,
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        array $metadata = [],
        ?int $exceptUserId = null,
    ): void {
        foreach ($users as $user) {
            if (
                $user instanceof User
                && $user->is_active
                && $user->id !== $exceptUserId
            ) {
                $this->notifyUser(
                    $user,
                    $category,
                    $title,
                    $message,
                    $url,
                    $metadata
                );
            }
        }
    }

    public function notifyRoles(
        array $roles,
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        array $metadata = [],
        ?int $exceptUserId = null,
    ): void {
        $users = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) =>
                $query->whereIn('name', $roles)
            )
            ->get();

        $this->notifyUsers(
            $users,
            $category,
            $title,
            $message,
            $url,
            $metadata,
            $exceptUserId
        );
    }

    public function notifyPermissions(
        array $permissions,
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        array $metadata = [],
        ?int $exceptUserId = null,
    ): void {
        $users = User::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($permissions) {
                $query
                    ->whereHas('roles.permissions', fn (Builder $roleQuery) =>
                        $roleQuery->whereIn('name', $permissions)
                    )
                    ->orWhereHas('permissions', fn (Builder $permissionQuery) =>
                        $permissionQuery->whereIn('name', $permissions)
                    );
            })
            ->distinct()
            ->get();

        $this->notifyUsers(
            $users,
            $category,
            $title,
            $message,
            $url,
            $metadata,
            $exceptUserId
        );
    }

    public function notifyAll(
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        array $metadata = [],
    ): void {
        $this->notifyUsers(
            User::query()
                ->where('is_active', true)
                ->get(),
            $category,
            $title,
            $message,
            $url,
            $metadata
        );
    }
}
