<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        $roles = $this->availableRoles();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [
                'string',
                'distinct',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $roleNames = $validated['roles'] ?? [];

        $this->ensurePrivilegedRolesAllowed($roleNames);

        DB::transaction(function () use ($validated, $roleNames) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $user->syncRoles($roleNames);
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->protectPrivilegedAccount($user);

        $user->load('roles', 'permissions');

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->protectPrivilegedAccount($user);

        $user->load('roles');

        $roles = $this->availableRoles();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->protectPrivilegedAccount($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [
                'string',
                'distinct',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $roleNames = array_key_exists('roles', $validated)
            ? $validated['roles']
            : $user->getRoleNames()->all();

        $this->ensurePrivilegedRolesAllowed($roleNames);

        if ($user->id === auth()->id()) {
            if (!$validated['is_active']) {
                throw ValidationException::withMessages([
                    'is_active' => 'You cannot deactivate your own account.',
                ]);
            }

            $currentRoles = $user->getRoleNames()->sort()->values()->all();
            $newRoles = collect($roleNames)->sort()->values()->all();

            if ($currentRoles !== $newRoles) {
                throw ValidationException::withMessages([
                    'roles' => 'You cannot change your own roles.',
                ]);
            }
        }

        $this->protectLastActiveSuperAdmin(
            $user,
            (bool) $validated['is_active'],
            $roleNames
        );

        DB::transaction(function () use ($validated, $user, $roleNames) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->is_active = $validated['is_active'];

            if (!empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->save();
            $user->syncRoles($roleNames);
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->protectPrivilegedAccount($user);

        if ($user->id === auth()->id()) {
            throw ValidationException::withMessages([
                'user' => 'You cannot deactivate your own account.',
            ]);
        }

        if ($user->hasRole('Super Admin') && $user->is_active) {
            $this->protectLastActiveSuperAdmin($user, false, []);
        }

        $user->update(['is_active' => false]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User deactivated successfully.');
    }

    private function availableRoles()
    {
        $query = Role::query()
            ->where('guard_name', 'web');

        if (!auth()->user()->hasRole('Super Admin')) {
            $query->whereNotIn('name', ['Admin', 'Super Admin']);
        }

        return $query->orderBy('name')->get();
    }

    private function ensurePrivilegedRolesAllowed(array $roleNames): void
    {
        $hasPrivilegedRole = collect($roleNames)
            ->contains(fn ($role) => in_array(
                $role,
                ['Admin', 'Super Admin'],
                true
            ));

        if ($hasPrivilegedRole && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Only a Super Admin can assign Admin or Super Admin roles.');
        }
    }

    private function protectPrivilegedAccount(User $user): void
    {
        if (
            ($user->hasRole('Super Admin') || $user->hasRole('Admin'))
            && !auth()->user()->hasRole('Super Admin')
        ) {
            abort(403, 'Only a Super Admin can manage Admin or Super Admin accounts.');
        }
    }

    private function protectLastActiveSuperAdmin(
        User $user,
        bool $willRemainActive,
        array $newRoleNames
    ): void {
        if (!$user->hasRole('Super Admin')) {
            return;
        }

        $willRemainSuperAdmin = in_array(
            'Super Admin',
            $newRoleNames,
            true
        );

        if ($willRemainActive && $willRemainSuperAdmin) {
            return;
        }

        $otherActiveSuperAdmins = User::role('Super Admin')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->count();

        if ($otherActiveSuperAdmins < 1) {
            throw ValidationException::withMessages([
                'roles' => 'You cannot deactivate or remove the role from the last active Super Admin.',
            ]);
        }
    }
}
