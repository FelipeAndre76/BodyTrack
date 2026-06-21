@extends('layouts.admin')

@section('title', 'Admin - BodyTrack')

@section('content')

<div class="admin-title">
    Painel Administrativo
</div>

<div class="admin-dashboard-grid">
    <div class="admin-dashboard-card">
        <span>Usuários</span>
        <strong>{{ $totalUsers }}</strong>
    </div>

    <div class="admin-dashboard-card">
        <span>Administradores</span>
        <strong>{{ $totalAdmins }}</strong>
    </div>

    <div class="admin-dashboard-card">
        <span>Exercícios</span>
        <strong>{{ $totalExercises }}</strong>
    </div>

    <div class="admin-dashboard-card">
        <span>Categorias</span>
        <strong>{{ $totalCategories }}</strong>
    </div>

    <div class="admin-dashboard-card">
        <span>Treinos</span>
        <strong>{{ $totalWorkouts }}</strong>
    </div>

    <div class="admin-dashboard-card">
        <span>Com Foto</span>
        <strong>{{ $exercisesWithPhoto }}</strong>
    </div>

    <div class="admin-dashboard-card warning">
        <span>Sem Foto</span>
        <strong>{{ $exercisesWithoutPhoto }}</strong>
    </div>
</div>

<div class="admin-dashboard-panel admin-progress-row">
    <h3>Progresso das Fotos</h3>

    <span class="admin-mini-badge">
        {{ $photoProgress }}% concluído
    </span>

    <div class="admin-progress-bar">
        <div style="width: {{ $photoProgress }}%"></div>
    </div>

    <small class="text-secondary">
        {{ $exercisesWithPhoto }} com foto / {{ $totalExercises }} exercícios
    </small>
</div>

<div class="admin-grid mb-5">
    <a href="{{ route('admin.exercises.index') }}" class="admin-history-card">
        <div class="admin-card-number">01</div>

        <div class="admin-card-image">
            <i class="bi bi-activity"></i>
        </div>

        <div class="admin-card-body">
            <span>Gerenciar</span>
            <h3>Exercícios</h3>
            <p>Cadastro, edição e fotos dos aparelhos.</p>
        </div>
    </a>

    <a href="{{ route('admin.categories.index') }}" class="admin-history-card">
        <div class="admin-card-number">02</div>

        <div class="admin-card-image">
            <i class="bi bi-tags"></i>
        </div>

        <div class="admin-card-body">
            <span>Gerenciar</span>
            <h3>Categorias</h3>
            <p>Grupos musculares e ícones do sistema.</p>
        </div>
    </a>

    <a href="{{ route('admin.photos.index', ['status' => 'without-photo']) }}" class="admin-history-card">
        <div class="admin-card-number">03</div>

        <div class="admin-card-image">
            <i class="bi bi-image"></i>
        </div>

        <div class="admin-card-body">
            <span>Fotos</span>
            <h3>Sem Foto</h3>
            <p>Exercícios que ainda precisam de imagem.</p>
        </div>
    </a>

    <a href="{{ route('admin.logs.index') }}" class="admin-history-card">
        <div class="admin-card-number">04</div>

        <div class="admin-card-image">
            <i class="bi bi-list-check"></i>
        </div>

        <div class="admin-card-body">
            <span>Auditoria</span>
            <h3>Logs</h3>
            <p>Histórico das ações administrativas.</p>
        </div>
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="admin-dashboard-panel">
            <h3>Últimas Atividades</h3>

            <div class="admin-mini-list">
                @forelse($latestLogs as $log)
                    <div class="admin-mini-item">
                        <div>
                            <strong>{{ $log->admin->name ?? 'Admin removido' }}</strong>
                            <span>{{ $log->description }}</span>
                        </div>

                        <span class="admin-mini-badge">
                            {{ $log->created_at->format('d/m H:i') }}
                        </span>
                    </div>
                @empty
                    <p class="text-secondary mb-0">Nenhuma atividade registrada.</p>
                @endforelse
            </div>

            <a href="{{ route('admin.logs.index') }}" class="btn-admin mt-4">
                Ver todos os logs
            </a>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="admin-dashboard-panel">
            <h3>Exercícios Sem Foto</h3>

            <div class="admin-mini-list">
                @forelse($latestExercisesWithoutPhoto as $exercise)
                    <div class="admin-mini-item">
                        <div>
                            <strong>{{ $exercise->name }}</strong>
                            <span>{{ $exercise->category->name ?? 'Sem categoria' }}</span>
                        </div>

                        <span class="admin-mini-badge">
                            Sem foto
                        </span>
                    </div>
                @empty
                    <p class="text-secondary mb-0">Todos os exercícios possuem foto.</p>
                @endforelse
            </div>

            <a href="{{ route('admin.photos.index', ['status' => 'without-photo']) }}"
               class="btn-admin mt-4">
                Ver todos sem foto
            </a>
        </div>
    </div>
</div>

@endsection
