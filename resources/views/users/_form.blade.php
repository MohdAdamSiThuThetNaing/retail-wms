@php
    $selectedRoles = old(
        'roles',
        isset($user) ? $user->roles->pluck('name')->all() : []
    );

    $isActive = old(
        'is_active',
        isset($user) ? $user->is_active : true
    );
@endphp

<div class="users-form-grid">

    <div class="users-form-section">
        <h3>Account Information</h3>
        <p>Enter the user's basic account details.</p>

        <div class="users-form-field">
            <label for="name">Full Name <span>*</span></label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $user->name ?? '') }}"
                placeholder="Enter full name"
                required
                maxlength="255"
            >
            @error('name') <small class="users-field-error">{{ $message }}</small> @enderror
        </div>

        <div class="users-form-field">
            <label for="email">Email Address <span>*</span></label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $user->email ?? '') }}"
                placeholder="name@example.com"
                required
                maxlength="255"
            >
            @error('email') <small class="users-field-error">{{ $message }}</small> @enderror
        </div>

        <div class="users-form-row">
            <div class="users-form-field">
                <label for="password">
                    Password
                    @if(!isset($user)) <span>*</span> @endif
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Enter password' }}"
                    {{ isset($user) ? '' : 'required' }}
                >
                @error('password') <small class="users-field-error">{{ $message }}</small> @enderror
            </div>

            <div class="users-form-field">
                <label for="password_confirmation">
                    Confirm Password
                    @if(!isset($user)) <span>*</span> @endif
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Confirm password"
                    {{ isset($user) ? '' : 'required' }}
                >
            </div>
        </div>
    </div>

    <div class="users-form-section">
        <h3>Roles & Permissions</h3>
        <p>Select the roles this user should have.</p>

        <div class="users-role-options">
            @forelse($roles as $role)
                <label class="users-role-option">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        @checked(in_array($role->name, $selectedRoles))
                    >

                    <span>
                        <strong>{{ $role->name }}</strong>
                        <small>Assign this role to the user</small>
                    </span>
                </label>
            @empty
                <p class="users-muted">No assignable roles are available.</p>
            @endforelse
        </div>

        @error('roles') <small class="users-field-error">{{ $message }}</small> @enderror
        @error('roles.*') <small class="users-field-error">{{ $message }}</small> @enderror
    </div>

    <div class="users-form-section users-account-status">
        <h3>Account Status</h3>
        <p>Inactive accounts cannot sign in.</p>

        <label class="users-switch-row">
            <input type="hidden" name="is_active" value="0">

            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked($isActive)
            >

            <span>
                <strong>Active Account</strong>
                <small>Allow this user to access the system.</small>
            </span>
        </label>
    </div>

</div>