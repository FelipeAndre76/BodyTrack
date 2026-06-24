@php
    $userNotifications = auth()->check()
        ? \App\Models\UserNotification::where('user_id', auth()->id())->latest()->limit(5)->get()
        : collect();

    $unreadNotificationsCount = auth()->check()
        ? \App\Models\UserNotification::where('user_id', auth()->id())->whereNull('read_at')->count()
        : 0;

    $latestNotificationId = $userNotifications->max('id') ?? 0;
@endphp

@auth
    <div class="notification-bell dropdown">
        <button class="notification-bell-btn"
                type="button"
                data-bs-toggle="dropdown"
                data-latest-id="{{ $latestNotificationId }}"
                aria-expanded="false"
                aria-label="Notificações">
            <i class="bi bi-bell"></i>

            @if($unreadNotificationsCount > 0)
                <span id="notificationBellCount">{{ $unreadNotificationsCount }}</span>
            @endif
        </button>

        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
            <div class="notification-dropdown-header">
                <strong>Notificações</strong>
                <small id="notificationUnreadText">{{ $unreadNotificationsCount }} nova(s)</small>
            </div>

            <div class="notification-actions {{ $userNotifications->isEmpty() ? 'd-none' : '' }}" id="notificationActions">
                <a href="{{ route('notifications.index') }}">
                    Ver todas
                </a>

                <button type="button"
                        data-notification-action="read"
                        data-url="{{ route('notifications.read-all') }}">
                    Marcar como lidas
                </button>

                <button type="button"
                        data-notification-action="clear"
                        data-url="{{ route('notifications.clear') }}">
                    Limpar tudo
                </button>
            </div>

            <div id="notificationList">
                @forelse($userNotifications as $notification)
                    <a href="{{ $notification->link_url ?: '#' }}"
                       class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                        <i class="bi {{ $notification->icon }}"></i>

                        <span>
                            <strong>{{ $notification->title }}</strong>
                            <small>{{ $notification->message }}</small>
                        </span>
                    </a>
                @empty
                    <div class="notification-empty">
                        Nenhuma notificação ainda.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endauth
