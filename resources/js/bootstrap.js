import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.Pusher = Pusher;

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

window.Echo = new Echo({
    broadcaster: "reverb",

    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: import.meta.env.VITE_REVERB_HOST || "localhost",
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8082),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8082),

    forceTLS: false,
    enabledTransports: ["ws"],

    disableStats: true,

    authEndpoint: "/broadcasting/auth",

    auth: {
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        },
    },
});
