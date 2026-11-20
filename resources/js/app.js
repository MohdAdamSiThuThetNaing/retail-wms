import "./bootstrap";

function initializeRealtime() {
    const userId = document
        .querySelector('meta[name="user-id"]')
        ?.getAttribute("content");

    const status = document.getElementById("socket-status");
    const testButton = document.getElementById("send-realtime-test");
    const messageBox = document.getElementById("socket-message");

    const bell = document.getElementById("notification-bell");
    const badge = document.getElementById("notification-badge");
    const dropdown = document.getElementById("notification-dropdown");
    const list = document.getElementById("notification-list");
    const summary = document.getElementById("notification-summary");
    const readAllButton = document.getElementById("notification-read-all");

    if (!userId || !window.Echo) {
        console.warn("Realtime initialization skipped.");
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    const api = window.axios.create({
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    });

    const categoryIcons = {
        inventory: "📦",
        purchase: "🛒",
        sales: "🧾",
        system: "⚙️",
    };

    let unreadCount = 0;
    let notificationItems = [];

    // Update notification badge
    function updateBadge(count) {
        unreadCount = Number(count) || 0;

        if (badge) {
            badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
            badge.hidden = unreadCount === 0;
        }

        if (summary) {
            summary.textContent = `${unreadCount} unread notification${
                unreadCount === 1 ? "" : "s"
            }`;
        }
    }

    // Render notification dropdown
    function renderNotifications() {
        if (!list) return;

        list.replaceChildren();

        if (!notificationItems.length) {
            const empty = document.createElement("div");
            empty.className = "notification-empty";
            empty.textContent = "You have no notifications.";
            list.appendChild(empty);
            return;
        }

        notificationItems.forEach((item) => {
            const link = document.createElement("a");

            link.className = `notification-item ${
                item.read_at ? "" : "unread"
            }`;

            link.href = item.url || "#";

            const icon = document.createElement("div");
            icon.className = "notification-item-icon";
            icon.textContent = categoryIcons[item.category] || "🔔";

            const content = document.createElement("div");
            content.className = "notification-item-content";

            const title = document.createElement("strong");
            title.textContent = item.title || "Notification";

            const message = document.createElement("p");
            message.textContent = item.message || "";

            const time = document.createElement("small");
            time.textContent = item.created_at
                ? new Date(item.created_at).toLocaleString()
                : "";

            content.append(title, message, time);
            link.append(icon, content);

            link.addEventListener("click", async (event) => {
                event.preventDefault();

                if (!item.read_at) {
                    try {
                        const response = await api.patch(
                            `/notifications/${encodeURIComponent(item.id)}/read`
                        );

                        item.read_at = new Date().toISOString();

                        updateBadge(response.data.unread_count);
                        renderNotifications();
                    } catch (error) {
                        console.error(
                            "Unable to mark notification as read:",
                            error
                        );

                        return;
                    }
                }

                if (item.url) {
                    window.location.href = item.url;
                }
            });

            list.appendChild(link);
        });
    }

    // Load notification history
    async function loadNotifications() {
        try {
            const response = await api.get("/notifications/data");

            notificationItems = response.data.notifications || [];

            updateBadge(response.data.unread_count || 0);
            renderNotifications();
        } catch (error) {
            console.error("Unable to load notifications:", error);

            if (list) {
                list.textContent = "Unable to load notifications.";
            }
        }
    }

    // Refresh authoritative unread count
    async function refreshUnreadCount() {
        try {
            const response = await api.get("/notifications/unread-count");

            updateBadge(response.data.unread_count);
        } catch (error) {
            console.error("Unable to refresh unread count:", error);
        }
    }

    // Toggle notification dropdown
    bell?.addEventListener("click", async () => {
        if (!dropdown) return;

        const opening = dropdown.hidden;

        dropdown.hidden = !opening;

        bell.setAttribute("aria-expanded", String(opening));

        if (opening) {
            await loadNotifications();
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (event) => {
        const center = document.getElementById("notification-center");

        if (dropdown && bell && center && !center.contains(event.target)) {
            dropdown.hidden = true;
            bell.setAttribute("aria-expanded", "false");
        }
    });

    // Mark all notifications as read
    readAllButton?.addEventListener("click", async () => {
        try {
            const response = await api.patch("/notifications/read-all");

            notificationItems = notificationItems.map((item) => ({
                ...item,
                read_at: item.read_at || new Date().toISOString(),
            }));

            updateBadge(response.data.unread_count);
            renderNotifications();
        } catch (error) {
            console.error("Unable to mark all notifications as read:", error);
        }
    });

    // Load initial notifications
    loadNotifications();

    // Connect to authenticated private user channel
    const channel = window.Echo.private(`App.Models.User.${userId}`);

    channel.subscribed(() => {
        console.log("Successfully subscribed to user channel.");

        if (status) {
            status.textContent = "🟢 Connected to Reverb WebSocket";
        }
    });

    channel.error((error) => {
        console.error("Private channel authentication failed:", error);

        if (status) {
            status.textContent = "🔴 Private channel authentication failed";
        }
    });

    // Listen for Laravel database notifications
    channel.notification((notification) => {
        console.log("Real-time WMS notification:", notification);

        notificationItems.unshift({
            id: notification.id,
            category: notification.category || "system",
            title: notification.title || "Notification",
            message: notification.message || "",
            url: notification.url || null,
            metadata: notification.metadata || {},
            read_at: null,
            created_at: notification.created_at || new Date().toISOString(),
        });

        notificationItems = notificationItems.slice(0, 30);

        updateBadge(unreadCount + 1);
        renderNotifications();

        refreshUnreadCount();

        if (messageBox) {
            const message = document.createElement("p");
            message.textContent = notification.title || "New notification";

            messageBox.prepend(message);
        }
    });

    // Monitor Reverb connection state
    const connection = window.Echo.connector.pusher.connection;

    connection.bind("state_change", (states) => {
        console.log("Reverb connection:", states.current);

        if (status && states.current !== "connected") {
            status.textContent = `🟡 Reverb connection: ${states.current}`;
        }
    });

    connection.bind("error", (error) => {
        console.error("Reverb connection error:", error);

        if (status) {
            status.textContent = "🔴 Reverb connection error";
        }
    });

    // Existing WebSocket test button
    testButton?.addEventListener("click", async () => {
        testButton.disabled = true;
        testButton.textContent = "Sending...";

        try {
            const response = await api.post("/realtime-test");

            console.log("Test notification response:", response.data);
        } catch (error) {
            console.error("Test notification failed:", error);

            alert(
                error.response?.data?.message ||
                    "Unable to send test notification."
            );
        } finally {
            testButton.disabled = false;
            testButton.textContent = "🔔 Send Test Socket Notification";
        }
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeRealtime);
} else {
    initializeRealtime();
}
