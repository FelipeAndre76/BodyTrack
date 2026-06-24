<div class="log-premium-timeline">
    @forelse($logs as $log)
        @php
            $icon = 'bi-list-check';
            $typeClass = 'default';

            if ($log->action === 'toggle_admin') {
                $icon = 'bi-shield-check';
                $typeClass = 'security';
            } elseif ($log->action === 'toggle_status') {
                $icon = 'bi-slash-circle';
                $typeClass = 'warning';
            } elseif ($log->action === 'delete_user') {
                $icon = 'bi-trash';
                $typeClass = 'danger';
            } elseif(str_contains($log->action, 'photo')) {
                $icon = 'bi-image';
                $typeClass = 'photo';
            } elseif(str_contains($log->action, 'exercise')) {
                $icon = 'bi-activity';
                $typeClass = 'exercise';
            } elseif(str_contains($log->action, 'category')) {
                $icon = 'bi-tags';
                $typeClass = 'category';
            }
        @endphp

        <div class="log-timeline-item {{ $typeClass }}"
             data-search="{{ strtolower(($log->admin->name ?? 'admin removido') . ' ' . $log->action . ' ' . $log->description) }}">

            <div class="log-timeline-icon">
                <i class="bi {{ $icon }}"></i>
            </div>

            <div class="log-timeline-card">
                <div class="log-timeline-header">
                    <div>
                        <strong>{{ $log->admin->name ?? 'Admin removido' }}</strong>
                        <span>{{ $log->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                    </div>

                    <small>
                        {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                    </small>
                </div>

                <p>{{ $log->description }}</p>
            </div>
        </div>
    @empty
        <div class="admin-empty-state">
            Nenhum log administrativo registrado.
        </div>
    @endforelse
</div>

<div class="photo-pagination-wrapper">
    {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>
