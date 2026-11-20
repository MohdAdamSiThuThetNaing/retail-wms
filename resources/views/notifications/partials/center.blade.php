
<div class="notification-center" id="notification-center">

    <button
        type="button"
        class="notification-bell"
        id="notification-bell"
        aria-label="Open notifications"
        aria-expanded="false"
    >
        <span class="notification-bell-icon">♧</span>

        <span
            class="notification-badge"
            id="notification-badge"
            hidden
        >0</span>
    </button>

    <div
        class="notification-dropdown"
        id="notification-dropdown"
        hidden
    >
        <div class="notification-dropdown-header">
            <div>
                <strong>Notifications</strong>
                <small id="notification-summary">
                    Loading...
                </small>
            </div>

            <button
                type="button"
                id="notification-read-all"
                class="notification-text-button"
            >
                Mark all read
            </button>
        </div>

        <div
            class="notification-list"
            id="notification-list"
            aria-live="polite"
        >
            <div class="notification-empty">
                Loading notifications...
            </div>
        </div>

        <a
            href="{{ route('notifications.history') }}"
            class="notification-footer"
        >
            View notification history
        </a>
    </div>

</div>
