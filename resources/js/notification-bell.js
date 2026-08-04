export default function notificationBell(unreadUrl) {
    return {
        count: 0,
        items: [],
        open: false,

        init() {
            this.fetchUnread();
            setInterval(() => this.fetchUnread(), 15000);
        },

        async fetchUnread() {
            const res = await fetch(unreadUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();
            this.count = data.count;
            this.items = data.items;
        },
    };
}
