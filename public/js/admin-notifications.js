(() => {
    const root = document.getElementById('adminNotifications');
    if (!root) return;

    const button = document.getElementById('notificationButton');
    const menu = document.getElementById('notificationMenu');
    const list = document.getElementById('notificationList');
    const count = document.getElementById('notificationCount');
    const toast = document.getElementById('orderToast');
    const storageKey = `admin-order-notifications-${root.dataset.adminId}`;
    const cursorKey = `${storageKey}-cursor`;
    const orderUrl = root.dataset.orderUrlTemplate;
    let notifications = JSON.parse(localStorage.getItem(storageKey) || '[]');
    let toastTimer;

    function saveNotifications() {
        localStorage.setItem(storageKey, JSON.stringify(notifications));
    }

    function renderNotifications() {
        list.replaceChildren();
        count.hidden = notifications.length === 0;
        count.textContent = notifications.length > 99 ? '99+' : String(notifications.length);

        if (!notifications.length) {
            const empty = document.createElement('p');
            empty.className = 'notification-empty';
            empty.textContent = 'No new purchases.';
            list.append(empty);
            return;
        }

        [...notifications].reverse().forEach(notification => {
            const link = document.createElement('a');
            link.className = 'notification-item';
            link.href = orderUrl.replace('__ORDER_ID__', encodeURIComponent(notification.id));
            const title = document.createElement('strong');
            title.textContent = `Order ${notification.number}`;
            const details = document.createElement('span');
            details.textContent = `${notification.buyer} · ${notification.items} item${notification.items === 1 ? '' : 's'} · ₱${Number(notification.total).toLocaleString('en-PH')}`;
            link.append(title, details);
            link.addEventListener('click', () => {
                notifications = notifications.filter(item => item.id !== notification.id);
                saveNotifications();
            });
            list.append(link);
        });
    }

    function showToast(notification) {
        toast.replaceChildren();
        const title = document.createElement('strong');
        title.textContent = 'New purchase received';
        const link = document.createElement('a');
        link.href = orderUrl.replace('__ORDER_ID__', encodeURIComponent(notification.id));
        link.textContent = `${notification.number} from ${notification.buyer} · ₱${Number(notification.total).toLocaleString('en-PH')}`;
        toast.append(title, link);
        toast.hidden = false;
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => {
            toast.hidden = true;
        }, 7000);
    }

    async function checkForOrders() {
        if (document.visibilityState !== 'visible') return;

        const cursor = localStorage.getItem(cursorKey);
        const url = new URL(root.dataset.endpoint, window.location.origin);
        if (cursor !== null) url.searchParams.set('after', cursor);

        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        });
        if (!response.ok) throw new Error(`Order notification request failed with status ${response.status}.`);
        const payload = await response.json();

        localStorage.setItem(cursorKey, String(payload.cursor));
        if (cursor === null || !payload.orders.length) return;

        const knownIds = new Set(notifications.map(notification => notification.id));
        const freshOrders = payload.orders.filter(order => !knownIds.has(order.id));
        if (!freshOrders.length) return;

        notifications = [...notifications, ...freshOrders].slice(-30);
        saveNotifications();
        renderNotifications();
        showToast(freshOrders[freshOrders.length - 1]);
    }

    renderNotifications();
    checkForOrders().catch(error => console.error('Unable to check for new orders.', error));
    window.setInterval(() => {
        checkForOrders().catch(error => console.error('Unable to check for new orders.', error));
    }, 10000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            checkForOrders().catch(error => console.error('Unable to check for new orders.', error));
        }
    });

    button.addEventListener('click', () => {
        const open = button.getAttribute('aria-expanded') !== 'true';
        button.setAttribute('aria-expanded', String(open));
        menu.hidden = !open;
    });
    document.getElementById('clearNotifications').addEventListener('click', () => {
        notifications = [];
        saveNotifications();
        renderNotifications();
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('#adminNotifications')) {
            menu.hidden = true;
            button.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !menu.hidden) {
            menu.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            button.focus();
        }
    });
})();
