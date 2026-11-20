<?php
namespace App\Services;

use App\Models\User;
use App\Notifications\WmsNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
    ): void {
        foreach ($users as $user) {
            if ($user instanceof User) {
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
    ): void {
        $users = User::query()
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
            $metadata
        );
    }

    public function notifyPermissions(
        array $permissions,
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        array $metadata = [],
    ): void {
        $users = User::query()
            ->whereHas('roles.permissions', fn (Builder $query) =>
                $query->whereIn('name', $permissions)
            )
            ->orWhereHas('permissions', fn (Builder $query) =>
                $query->whereIn('name', $permissions)
            )
            ->distinct()
            ->get();

        $this->notifyUsers(
            $users,
            $category,
            $title,
            $message,
            $url,
            $metadata
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
            User::query()->get(),
            $category,
            $title,
            $message,
            $url,
            $metadata
        );
    }
}
