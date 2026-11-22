
@extends('layouts.app')

@section('title', 'Notification Center')

@section('content')

<div class="notification-history-page">

    {{-- Page Header --}}
    <div class="notification-history-header">
        <div>
            <h1>Notification Center</h1>
            <p>View and manage your system and operational notifications.</p>
        </div>

        <button
            type="button"
            id="mark-all-read"
            class="history-mark-all">
            ✓ Mark All as Read
        </button>
    </div>

    {{-- Notification History --}}
    <div id="notification-history-list">
        <div class="history-loading">
            Loading notifications...
        </div>
    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('DOMContentLoaded', () => {

    const list = document.getElementById('notification-history-list');
    const markAllButton = document.getElementById('mark-all-read');

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.content;

    if (!list || !markAllButton) {
        console.error('Notification Center elements not found.');
        return;
    }

    // Escape HTML to prevent unsafe content rendering.
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    // Format notification date.
    function formatDate(date) {
        if (!date) return '';

        const parsedDate = new Date(date);

        if (Number.isNaN(parsedDate.getTime())) {
            return '';
        }

        return parsedDate.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Category icons.
    function getCategoryIcon(category) {
        const icons = {
            inventory: '📦',
            purchase: '🛒',
            sales: '🧾',
            system: '⚙️'
        };

        return icons[category] || '🔔';
    }

    // Render notification history.
    function renderNotifications(notifications) {

        if (!notifications.length) {
            list.innerHTML = `
                <div class="history-empty">
                    <div class="history-empty-icon">🔔</div>
                    <h3>You're all caught up!</h3>
                    <p>You don't have any notifications yet.</p>
                </div>
            `;
            return;
        }

        list.innerHTML = notifications.map(notification => {

            const unread = !notification.read_at;
            const category = notification.category || 'system';

            const safeUrl = (() => {
                try {
                    const parsed = new URL(
                        notification.url || '',
                        window.location.origin
                    );

                    return parsed.origin === window.location.origin
                        ? parsed.href
                        : '';
                } catch {
                    return '';
                }
            })();

            return `
                <article class="history-card ${unread ? 'unread' : ''}">

                    <div class="history-icon">
                        ${getCategoryIcon(category)}
                    </div>

                    <div class="history-content">

                        <h3 class="history-title">
                            ${escapeHtml(notification.title || 'Notification')}
                        </h3>

                        <p class="history-message">
                            ${escapeHtml(notification.message || '')}
                        </p>

                        <div class="history-meta">

                            <span class="history-category">
                                ${escapeHtml(category)}
                            </span>

                            <span>
                                ${escapeHtml(formatDate(notification.created_at))}
                            </span>

                        </div>

                        <div class="history-actions">

                            ${safeUrl ? `
                                <a
                                    href="${escapeHtml(safeUrl)}"
                                    class="history-action">
                                    View Details
                                </a>
                            ` : ''}

                            ${unread ? `
                                <button
                                    type="button"
                                    class="history-action mark-read"
                                    data-id="${escapeHtml(notification.id)}">
                                    Mark as Read
                                </button>
                            ` : ''}

                        </div>

                    </div>

                    <span class="history-status ${unread ? 'unread' : 'read'}">
                        ${unread ? 'Unread' : 'Read'}
                    </span>

                </article>
            `;

        }).join('');
    }

    // Load notification history from Laravel API.
    async function loadNotifications() {

        list.innerHTML = `
            <div class="history-loading">
                Loading notifications...
            </div>
        `;

        try {

            const response = await fetch('/notifications/data', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error('Unable to retrieve notifications.');
            }

            const notifications = result.notifications || [];

            renderNotifications(notifications);

        } catch (error) {

            console.error('Notification loading failed:', error);

            list.innerHTML = `
                <div class="history-error">
                    Unable to load notifications.
                    Please refresh the page and try again.
                </div>
            `;
        }
    }

    // Mark individual notification as read.
    list.addEventListener('click', async event => {

        const button = event.target.closest('.mark-read');

        if (!button) return;

        const notificationId = button.dataset.id;

        button.disabled = true;
        button.textContent = 'Updating...';

        try {

            const response = await fetch(
                `/notifications/${encodeURIComponent(notificationId)}/read`,
                {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Unable to mark notification as read.');
            }

            await loadNotifications();

        } catch (error) {

            console.error(error);
            alert(error.message);

            button.disabled = false;
            button.textContent = 'Mark as Read';
        }
    });

    // Mark all notifications as read.
    markAllButton.addEventListener('click', async () => {

        markAllButton.disabled = true;
        markAllButton.textContent = 'Updating...';

        try {

            const response = await fetch('/notifications/read-all', {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                throw new Error('Unable to mark all notifications as read.');
            }

            await loadNotifications();

        } catch (error) {

            console.error(error);
            alert(error.message);

        } finally {

            markAllButton.disabled = false;
            markAllButton.textContent = '✓ Mark All as Read';
        }
    });

    // Initial notification loading.
    loadNotifications();

});
</script>

@endsection
