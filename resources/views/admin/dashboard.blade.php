@extends('layouts.admin')

@section('title', 'Admin - BodyTrack')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Central administrativa</span>
        <h1>Painel Administrativo</h1>
        <p>Visão executiva da base, catálogo, usuários, uso do app e movimentações recentes.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.exercises.create') }}" class="btn-admin">
            <i class="bi bi-plus-circle"></i>
            Novo exercício
        </a>

        <a href="{{ route('admin.photos.index', ['status' => 'without-photo']) }}" class="btn-admin-secondary">
            <i class="bi bi-camera"></i>
            Fotos pendentes
        </a>

        <a href="{{ route('admin.logs.index') }}" class="btn-admin-secondary">
            <i class="bi bi-list-check"></i>
            Logs
        </a>
    </div>
</div>

<div class="admin-premium-overview">
    <div class="admin-main-metric">
        <div>
            <span>Usuários cadastrados</span>
            <strong>{{ $totalUsers }}</strong>
            <small>{{ $totalActiveUsers }} ativo(s) / {{ $totalBlockedUsers }} bloqueado(s)</small>
        </div>

        <div class="admin-main-metric-icon">
            <i class="bi bi-people-fill"></i>
        </div>
    </div>

    <div class="admin-premium-stat">
        <i class="bi bi-shield-check"></i>
        <span>Admins</span>
        <strong>{{ $totalAdmins }}</strong>
    </div>

    <div class="admin-premium-stat">
        <i class="bi bi-activity"></i>
        <span>Exercícios</span>
        <strong>{{ $totalExercises }}</strong>
    </div>

    <div class="admin-premium-stat">
        <i class="bi bi-clipboard2-pulse-fill"></i>
        <span>Treinos</span>
        <strong>{{ $totalWorkouts }}</strong>
    </div>

    <div class="admin-premium-stat warning">
        <i class="bi bi-image"></i>
        <span>Sem foto</span>
        <strong>{{ $exercisesWithoutPhoto }}</strong>
    </div>
</div>

<div class="dashboard-action-grid mb-4">
    <a href="{{ route('admin.exercises.create') }}" class="dashboard-action-card">
        <i class="bi bi-plus-circle"></i>
        <span>Novo exercício</span>
    </a>

    <a href="{{ route('admin.categories.create') }}" class="dashboard-action-card">
        <i class="bi bi-tags"></i>
        <span>Nova categoria</span>
    </a>

    <a href="{{ route('admin.photos.index', ['status' => 'without-photo']) }}" class="dashboard-action-card warning">
        <i class="bi bi-camera"></i>
        <span>Corrigir fotos</span>
    </a>

    <a href="{{ route('admin.logs.pdf') }}" class="dashboard-action-card">
        <i class="bi bi-file-earmark-pdf"></i>
        <span>Exportar logs</span>
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Últimos 30 dias</span>
                    <h3>Evolução diária</h3>
                </div>

                <strong class="admin-panel-value">{{ $adminActionsLast30Days }}</strong>
            </div>

            <div id="adminDailyActivityChart"></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Saúde do catálogo</span>
                    <h3>Qualidade geral</h3>
                </div>

                <strong class="admin-panel-value">{{ $catalogHealth }}%</strong>
            </div>

            <div id="catalogHealthChart"></div>

            <div class="admin-photo-summary">
                <div>
                    <span>Fotos completas</span>
                    <strong>{{ $photoProgress }}%</strong>
                </div>

                <div>
                    <span>Categorias vazias</span>
                    <strong>{{ $emptyCategories }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-usage-grid mb-4">
    <div class="dashboard-usage-card">
        <i class="bi bi-person-plus"></i>
        <span>Novos usuários</span>
        <strong>{{ $newUsersLast30Days }}</strong>
        <small>últimos 30 dias</small>
    </div>

    <div class="dashboard-usage-card">
        <i class="bi bi-activity"></i>
        <span>Novos exercícios</span>
        <strong>{{ $newExercisesLast30Days }}</strong>
        <small>últimos 30 dias</small>
    </div>

    <div class="dashboard-usage-card">
        <i class="bi bi-clipboard2-pulse"></i>
        <span>Treinos criados</span>
        <strong>{{ $workoutsLast30Days }}</strong>
        <small>últimos 30 dias</small>
    </div>

    <div class="dashboard-usage-card">
        <i class="bi bi-egg-fried"></i>
        <span>Refeições</span>
        <strong>{{ $mealsLast30Days }}</strong>
        <small>últimos 30 dias</small>
    </div>

    <div class="dashboard-usage-card">
        <i class="bi bi-droplet"></i>
        <span>Água</span>
        <strong>{{ $waterLogsLast30Days }}</strong>
        <small>registros</small>
    </div>

    <div class="dashboard-usage-card">
        <i class="bi bi-speedometer2"></i>
        <span>Pesagens</span>
        <strong>{{ $weightLogsLast30Days }}</strong>
        <small>registros</small>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Pendências</span>
                    <h3>Atenção rápida</h3>
                </div>
            </div>

            <div class="dashboard-pending-list">
                <a href="{{ route('admin.photos.index', ['status' => 'without-photo']) }}" class="dashboard-pending-item warning">
                    <i class="bi bi-camera"></i>
                    <div>
                        <strong>{{ $exercisesWithoutPhoto }} exercício(s) sem foto</strong>
                        <span>Complete a biblioteca visual</span>
                    </div>
                </a>

                <a href="{{ route('admin.categories.index', ['status' => 'empty']) }}" class="dashboard-pending-item">
                    <i class="bi bi-tags"></i>
                    <div>
                        <strong>{{ $emptyCategories }} categoria(s) vazia(s)</strong>
                        <span>Organize o catálogo</span>
                    </div>
                </a>

                <a href="{{ route('admin.users.index', ['status' => 'blocked']) }}" class="dashboard-pending-item danger">
                    <i class="bi bi-slash-circle"></i>
                    <div>
                        <strong>{{ $totalBlockedUsers }} usuário(s) bloqueado(s)</strong>
                        <span>Revise acessos</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Catálogo</span>
                    <h3>Top categorias</h3>
                </div>

                <a href="{{ route('admin.categories.index') }}" class="admin-text-link">
                    Ver tudo
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="admin-premium-list compact">
                @forelse($topCategories as $category)
                    <div class="admin-premium-list-item">
                        <div class="admin-list-icon">
                            <i class="bi {{ $category->icon ?? 'bi-tags' }}"></i>
                        </div>

                        <div>
                            <strong>{{ $category->name }}</strong>
                            <span>{{ $category->exercises_count }} exercício(s)</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty-state">
                        Nenhuma categoria cadastrada.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Usuários</span>
                    <h3>Bloqueados recentes</h3>
                </div>

                <a href="{{ route('admin.users.index', ['status' => 'blocked']) }}" class="admin-text-link">
                    Ver tudo
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="admin-premium-list compact">
                @forelse($latestBlockedUsers as $user)
                    <div class="admin-premium-list-item">
                        <div class="admin-list-icon warning">
                            <i class="bi bi-person-slash"></i>
                        </div>

                        <div>
                            <strong>{{ $user->name }}</strong>
                            <span>{{ $user->email }}</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty-state success">
                        Nenhum usuário bloqueado.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Auditoria</span>
                    <h3>Últimas ações administrativas</h3>
                </div>

                <a href="{{ route('admin.logs.index') }}" class="admin-text-link">
                    Ver tudo
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="admin-premium-list">
                @forelse($recentLogs as $log)
                    <div class="admin-premium-list-item">
                        <div class="admin-list-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <div>
                            <strong>{{ $log->admin->name ?? 'Admin removido' }}</strong>
                            <span>{{ $log->description }}</span>
                        </div>

                        <small>{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <div class="admin-empty-state">
                        Nenhuma ação administrativa recente.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="admin-premium-panel">
            <div class="admin-panel-header">
                <div>
                    <span>Qualidade do catálogo</span>
                    <h3>Exercícios sem foto</h3>
                </div>

                <a href="{{ route('admin.photos.index', ['status' => 'without-photo']) }}" class="admin-text-link">
                    Corrigir
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="admin-premium-list compact">
                @forelse($latestExercisesWithoutPhoto as $exercise)
                    <div class="admin-premium-list-item">
                        <div class="admin-list-icon warning">
                            <i class="bi bi-camera"></i>
                        </div>

                        <div>
                            <strong>{{ $exercise->name }}</strong>
                            <span>{{ $exercise->category->name ?? 'Sem categoria' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty-state success">
                        Todos os exercícios possuem foto.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dailyActivityChart = new ApexCharts(document.querySelector("#adminDailyActivityChart"), {
        chart: {
            type: 'area',
            height: 330,
            toolbar: { show: false },
            background: 'transparent',
            foreColor: '#9ca3af'
        },
        series: [
            {
                name: 'Usuários',
                data: @json($dailyUsers)
            },
            {
                name: 'Treinos',
                data: @json($dailyWorkouts)
            },
            {
                name: 'Ações admin',
                data: @json($dailyAdminActions)
            }
        ],
        xaxis: {
            categories: @json($activityLabels),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: .28,
                opacityTo: .03
            }
        },
        grid: {
            borderColor: 'rgba(163,230,53,.12)',
            strokeDashArray: 4
        },
        dataLabels: { enabled: false },
        colors: ['#a3e635', '#22c55e', '#f59e0b'],
        tooltip: { theme: 'dark' },
        legend: {
            labels: {
                colors: '#d1d5db'
            }
        }
    });

    dailyActivityChart.render();

    const catalogHealthChart = new ApexCharts(document.querySelector("#catalogHealthChart"), {
        chart: {
            type: 'radialBar',
            height: 280,
            background: 'transparent'
        },
        series: [{{ $catalogHealth }}],
        colors: ['#a3e635'],
        plotOptions: {
            radialBar: {
                hollow: {
                    size: '68%'
                },
                track: {
                    background: 'rgba(163,230,53,.10)'
                },
                dataLabels: {
                    name: {
                        show: true,
                        color: '#9ca3af',
                        fontSize: '13px',
                        fontWeight: 900
                    },
                    value: {
                        color: '#fff',
                        fontSize: '32px',
                        fontWeight: 900,
                        formatter: function (value) {
                            return Math.round(value) + '%';
                        }
                    }
                }
            }
        },
        labels: ['Saúde']
    });

    catalogHealthChart.render();
});
</script>
@endsection
