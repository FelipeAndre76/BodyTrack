<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'BodyTrack')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/bodytrack-dashboard.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    @livewireStyles

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>

@include('partials.sidebar')

<main class="main">
    @include('partials.topbar')
    @include('partials.notification-bar')

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
@livewireScripts
<script src="{{ asset('js/bodytrack-image-compressor.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function hideSystemNotificationBar(notificationBar) {
        if (!notificationBar) {
            return;
        }

        const notificationId = notificationBar.dataset.notificationId || 'current';
        const storageKey = `bodytrack_notification_closed_${notificationId}`;

        try {
            localStorage.setItem(storageKey, '1');
        } catch (error) {
            return;
        }

        notificationBar.classList.add('is-hidden');
        notificationBar.setAttribute('hidden', 'hidden');
    }

    document.querySelectorAll('[data-system-notification-bar]').forEach(function (notificationBar) {
        const notificationId = notificationBar.dataset.notificationId || 'current';
        const storageKey = `bodytrack_notification_closed_${notificationId}`;

        try {
            if (localStorage.getItem(storageKey) === '1') {
                notificationBar.classList.add('is-hidden');
                notificationBar.setAttribute('hidden', 'hidden');
            }
        } catch (error) {
            return;
        }
    });

    document.addEventListener('click', function (event) {
        const closeButton = event.target.closest('[data-system-notification-close]');

        if (!closeButton) {
            return;
        }

        event.preventDefault();
        hideSystemNotificationBar(closeButton.closest('[data-system-notification-bar]'));
    });

    function clearNotificationBadge() {
        document.getElementById('notificationBellCount')?.remove();
        document.querySelector('.notification-bell-btn')?.classList.remove('has-new');

        const unreadText = document.getElementById('notificationUnreadText');

        if (unreadText) {
            unreadText.innerText = '0 nova(s)';
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    const notificationState = {
        latestId: Number(document.querySelector('.notification-bell-btn')?.dataset.latestId || 0),
        booted: false,
        timer: null,
    };

    function showNotificationToast(notification) {
        if (!notification || !window.Swal || !notificationState.booted) {
            return;
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            titleText: notification.title,
            text: notification.message,
            showConfirmButton: false,
            timer: 3600,
            timerProgressBar: true,
            background: '#0b100d',
            color: '#ffffff',
        });
    }

    function renderNotifications(payload) {
        const list = document.getElementById('notificationList');
        const actions = document.getElementById('notificationActions');
        const unreadText = document.getElementById('notificationUnreadText');
        const bellButton = document.querySelector('.notification-bell-btn');
        const unreadCount = Number(payload.unread_notifications || 0);
        const latestId = Number(payload.latest_id || 0);
        const hasNewNotification = latestId > notificationState.latestId && unreadCount > 0;

        if (unreadText) {
            unreadText.innerText = unreadCount + ' nova(s)';
        }

        let badge = document.getElementById('notificationBellCount');

        if (unreadCount > 0 && bellButton) {
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'notificationBellCount';
                bellButton.appendChild(badge);
            }

            badge.innerText = unreadCount;
            bellButton.classList.toggle('has-new', hasNewNotification);
        } else {
            badge?.remove();
            bellButton?.classList.remove('has-new');
        }

        if (hasNewNotification) {
            showNotificationToast(payload.notifications?.[0]);
        }

        if (latestId > 0) {
            notificationState.latestId = latestId;
            bellButton?.setAttribute('data-latest-id', latestId);
        }

        notificationState.booted = true;

        if (!list) {
            return;
        }

        if (!payload.notifications || payload.notifications.length < 1) {
            list.innerHTML = '<div class="notification-empty">Nenhuma notificação ainda.</div>';
            actions?.classList.add('d-none');
            return;
        }

        actions?.classList.remove('d-none');
        list.innerHTML = payload.notifications.map(notification => `
            <a href="${escapeHtml(notification.link_url || '#')}"
               class="notification-item ${notification.unread ? 'unread' : ''}">
                <i class="bi ${escapeHtml(notification.icon || 'bi-bell')}"></i>
                <span>
                    <strong>${escapeHtml(notification.title)}</strong>
                    <small>${escapeHtml(notification.message)}</small>
                    ${notification.created_at ? `<em>${escapeHtml(notification.created_at)}</em>` : ''}
                </span>
            </a>
        `).join('');
    }

    async function refreshNotifications() {
        try {
            const response = await fetch('{{ route('notifications.latest') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                renderNotifications(await response.json());
            }
        } catch (error) {
            return;
        }
    }

    function scheduleNotificationRefresh() {
        if (!document.querySelector('.notification-bell')) {
            return;
        }

        window.clearTimeout(notificationState.timer);

        notificationState.timer = window.setTimeout(async function () {
            await refreshNotifications();
            scheduleNotificationRefresh();
        }, document.hidden ? 15000 : 5000);
    }

    if (document.querySelector('.notification-bell')) {
        refreshNotifications();
        scheduleNotificationRefresh();
        document.addEventListener('visibilitychange', scheduleNotificationRefresh);
        window.addEventListener('bodytrack:notifications-refresh', refreshNotifications);

        document.addEventListener('livewire:init', function () {
            Livewire.on('social-post-created', refreshNotifications);
            Livewire.on('social-status-created', refreshNotifications);
            Livewire.on('social-content-deleted', refreshNotifications);
        });
    }

    document.querySelectorAll('[data-notification-action]').forEach(button => {
        button.addEventListener('click', async function (event) {
            event.preventDefault();

            const action = button.dataset.notificationAction;
            const method = action === 'clear' ? 'DELETE' : 'PATCH';

            button.disabled = true;

            try {
                const response = await fetch(button.dataset.url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Não foi possível atualizar as notificações.');
                }

                clearNotificationBadge();

                if (action === 'read') {
                    document.querySelectorAll('.notification-item.unread')
                        .forEach(item => item.classList.remove('unread'));
                }

                if (action === 'clear') {
                    const list = document.getElementById('notificationList');
                    const actions = document.getElementById('notificationActions');

                    if (list) {
                        list.innerHTML = '<div class="notification-empty">Nenhuma notificação ainda.</div>';
                    }

                    actions?.classList.add('d-none');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ops',
                    text: error.message
                });
            } finally {
                button.disabled = false;
            }
        });
    });
});
</script>
@yield('scripts')

</body>
</html>
