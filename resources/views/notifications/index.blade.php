@extends('layouts.bodytrack')

@section('title', 'Notificações - BodyTrack')

@section('content')
@php
    $typeLabels = [
        'all' => 'Todos',
        'social' => 'Social',
        'nutrition' => 'Nutrição',
        'workout' => 'Treino',
        'system' => 'Sistema',
        'info' => 'Sistema',
    ];

    $statusLabels = [
        'all' => 'Todas',
        'unread' => 'Não lidas',
        'read' => 'Lidas',
    ];
@endphp

<section class="notifications-page">
    <div class="notifications-hero">
        <div>
            <span class="eyebrow">Central</span>
            <h1>Notificações</h1>
            <p>Acompanhe avisos do sistema, interações sociais, nutrição e treinos em um só lugar.</p>
        </div>

        <div class="notifications-hero-actions">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="button"
                        class="notification-page-btn secondary"
                        data-notification-submit
                        data-confirm-type="read-all"
                        data-confirm-title="Marcar todas como lidas?"
                        data-confirm-text="Todas as notificações pendentes serão marcadas como lidas.">
                    <i class="bi bi-check2-all"></i>
                    Marcar lidas
                </button>
            </form>

            <form method="POST" action="{{ route('notifications.clear') }}">
                @csrf
                @method('DELETE')
                <button type="button"
                        class="notification-page-btn danger"
                        data-notification-submit
                        data-confirm-type="clear"
                        data-confirm-title="Limpar todas as notificações?"
                        data-confirm-text="Essa ação remove todo o histórico de notificações da sua conta.">
                    <i class="bi bi-trash3"></i>
                    Limpar tudo
                </button>
            </form>
        </div>
    </div>

    <div class="notifications-stats">
        <div class="notification-stat-card">
            <span>Total</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>

        <div class="notification-stat-card highlight">
            <span>Não lidas</span>
            <strong>{{ $stats['unread'] }}</strong>
        </div>

        <div class="notification-stat-card">
            <span>Social</span>
            <strong>{{ $stats['social'] }}</strong>
        </div>

        <div class="notification-stat-card">
            <span>Sistema</span>
            <strong>{{ $stats['system'] }}</strong>
        </div>
    </div>

    <div class="notifications-toolbar">
        <div class="notification-filter-group">
            <span>Tipo</span>

            <a href="{{ route('notifications.index', ['type' => 'all', 'status' => $status]) }}"
               class="{{ $type === 'all' ? 'active' : '' }}">
                Todos
            </a>

            @foreach($availableTypes as $availableType)
                <a href="{{ route('notifications.index', ['type' => $availableType, 'status' => $status]) }}"
                   class="{{ $type === $availableType ? 'active' : '' }}">
                    {{ $typeLabels[$availableType] ?? ucfirst($availableType) }}
                </a>
            @endforeach
        </div>

        <div class="notification-filter-group">
            <span>Status</span>

            @foreach($statusLabels as $statusKey => $statusLabel)
                <a href="{{ route('notifications.index', ['type' => $type, 'status' => $statusKey]) }}"
                   class="{{ $status === $statusKey ? 'active' : '' }}">
                    {{ $statusLabel }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="notifications-list-panel">
        @forelse($notifications as $notification)
            <article class="notification-history-item {{ $notification->read_at ? '' : 'unread' }}">
                <a href="{{ $notification->link_url ?: '#' }}" class="notification-history-main">
                    <div class="notification-history-icon">
                        <i class="bi {{ $notification->icon ?: 'bi-bell' }}"></i>
                    </div>

                    <div>
                        <div class="notification-history-title">
                            <strong>{{ $notification->title }}</strong>
                            <span>{{ $typeLabels[$notification->type] ?? ucfirst($notification->type) }}</span>
                        </div>

                        <p>{{ $notification->message }}</p>

                        <small>
                            {{ $notification->created_at?->format('d/m/Y H:i') }}
                            @if(!$notification->read_at)
                                <b>Não lida</b>
                            @endif
                        </small>
                    </div>
                </a>

                <div class="notification-history-actions">
                    @if(!$notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                    title="Marcar como lida"
                                    data-notification-submit
                                    data-confirm-type="read-one"
                                    data-confirm-title="Marcar como lida?"
                                    data-confirm-text="Essa notificação sairá da lista de pendentes.">
                                <i class="bi bi-check2"></i>
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger" title="Excluir notificação">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="notifications-empty">
                <i class="bi bi-bell-slash"></i>
                <strong>Nenhuma notificação encontrada</strong>
                <span>Quando algo novo acontecer, os avisos aparecem aqui.</span>
            </div>
        @endforelse
    </div>

    <div class="notifications-pagination">
        {{ $notifications->links() }}
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.notifications-page');

    if (!page) {
        return;
    }

    if (!window.Swal) {
        page.querySelectorAll('[data-notification-submit]').forEach(function (button) {
            button.addEventListener('click', function () {
                button.closest('form')?.submit();
            });
        });

        return;
    }

    const configs = {
        'read-all': {
            icon: 'question',
            title: 'Marcar todas como lidas?',
            text: 'Todas as notificações pendentes serão marcadas como lidas.',
            confirmButtonText: 'Marcar lidas',
            confirmButtonColor: '#9ee62d',
        },
        'read-one': {
            icon: 'question',
            title: 'Marcar como lida?',
            text: 'Essa notificação sairá da lista de pendentes.',
            confirmButtonText: 'Marcar lida',
            confirmButtonColor: '#9ee62d',
        },
        clear: {
            icon: 'warning',
            title: 'Limpar todas as notificações?',
            text: 'Essa ação remove todo o histórico de notificações da sua conta.',
            confirmButtonText: 'Limpar tudo',
            confirmButtonColor: '#ef4444',
        },
        'delete-one': {
            icon: 'warning',
            title: 'Excluir notificação?',
            text: 'Essa notificação será removida do seu histórico.',
            confirmButtonText: 'Excluir',
            confirmButtonColor: '#ef4444',
        },
    };

    function formActionType(form, button = null) {
        if (button?.dataset.confirmType) {
            return button.dataset.confirmType;
        }

        const method = form.querySelector('input[name="_method"]')?.value?.toUpperCase() || form.method.toUpperCase();
        const action = form.getAttribute('action') || '';

        if (method === 'PATCH' && action.includes('/read-all')) {
            return 'read-all';
        }

        if (method === 'PATCH') {
            return 'read-one';
        }

        if (method === 'DELETE' && action.includes('/clear')) {
            return 'clear';
        }

        return 'delete-one';
    }

    async function confirmAndSubmit(form, button = null) {
        const type = formActionType(form, button);
        const config = configs[type] || configs['delete-one'];

        const result = await Swal.fire({
            icon: config.icon,
            title: button?.dataset.confirmTitle || config.title,
            text: button?.dataset.confirmText || config.text,
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: config.confirmButtonText,
            cancelButtonText: 'Cancelar',
            background: '#0b100d',
            color: '#ffffff',
            confirmButtonColor: config.confirmButtonColor,
            cancelButtonColor: '#1f2937',
            customClass: {
                popup: 'notification-swal-popup',
                confirmButton: 'notification-swal-confirm',
                cancelButton: 'notification-swal-cancel',
            },
        });

        if (!result.isConfirmed) {
            return;
        }

        form.dataset.confirmed = '1';
        button?.setAttribute('disabled', 'disabled');
        form.submit();
    }

    page.querySelectorAll('[data-notification-submit]').forEach(function (button) {
        button.addEventListener('click', function () {
            const form = button.closest('form');

            if (form) {
                confirmAndSubmit(form, button);
            }
        });
    });

    page.querySelectorAll('form[action*="/notifications"]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            confirmAndSubmit(form, event.submitter);
        });
    });
});
</script>
@endsection
