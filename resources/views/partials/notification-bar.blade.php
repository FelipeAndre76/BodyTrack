@php
    $systemNotification = \App\Models\SystemNotification::visible()
        ->latest()
        ->first();
@endphp

@if($systemNotification)
    <div class="system-notification-bar"
         data-notification-id="{{ $systemNotification->id }}"
         data-system-notification-bar>
        <div class="system-notification-content">
            <div class="system-notification-icon">
                <i class="bi {{ $systemNotification->icon }}"></i>
            </div>

            <div class="system-notification-text">
                <strong>{{ $systemNotification->title }}</strong>
                <span>{{ $systemNotification->message }}</span>
            </div>

            @if($systemNotification->link_label && $systemNotification->link_url)
                <a href="{{ $systemNotification->link_url }}" class="system-notification-link">
                    {{ $systemNotification->link_label }}
                </a>
            @endif
        </div>

        <button type="button"
                class="system-notification-close"
                data-system-notification-close
                aria-label="Fechar aviso">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
@endif
