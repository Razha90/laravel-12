import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});


// window.Echo.channel("notifications")
//     .listen(".notifications", (e) => {
//         try {
//             console.log("Pesan diterima:", e.message);
//         alert("Pesan baru: " + e.message);
//         } catch (error) {
//             alert("Pesan baru: ");
//         }
//     })
//     .on("pusher:subscription_succeeded", () => {
//         console.log("Berhasil subscribe ke channel notifications");
//     }).on("pusher:subscription_error", (status) => {
//         console.error("Gagal subscribe ke channel notifications:", status);
//     });;