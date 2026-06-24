@php
    use App\BodyMetrics;
    use App\Models\MealLog;
    use App\Models\WaterLog;
    use App\Models\Workout;

    $topbarUser = auth()->user();
    $topbarToday = now()->toDateString();
    $topbarMetrics = $topbarUser ? BodyMetrics::for($topbarUser->profile) : null;
    $topbarGoals = $topbarMetrics['nutrition_goals'] ?? ['protein' => 1, 'calories' => 1];

    $topbarNutrition = $topbarUser
        ? MealLog::where('user_id', $topbarUser->id)
            ->where('meal_date', $topbarToday)
            ->selectRaw('COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(calories), 0) as calories')
            ->first()
        : null;

    $topbarWater = $topbarUser
        ? WaterLog::where('user_id', $topbarUser->id)->where('recorded_at', $topbarToday)->sum('amount_ml')
        : 0;

    $topbarWorkoutDone = $topbarUser
        ? Workout::where('user_id', $topbarUser->id)->where('workout_date', $topbarToday)->exists()
        : false;

    $topbarProteinPercent = min(100, (($topbarNutrition->protein ?? 0) / max(1, $topbarGoals['protein'])) * 100);
    $topbarWaterPercent = min(100, ($topbarWater / max(1, $topbarMetrics['water_goal'] ?? 1)) * 100);
@endphp

<header class="topbar">
    <div class="topbar-left">
        <a href="{{ route('nutrition.index') }}" class="topbar-status-pill">
            <i class="bi bi-egg-fried"></i>
            <span>Proteína</span>
            <strong>{{ number_format($topbarProteinPercent, 0) }}%</strong>
        </a>

        <a href="{{ route('water.index') }}" class="topbar-status-pill">
            <i class="bi bi-droplet"></i>
            <span>Água</span>
            <strong>{{ number_format($topbarWaterPercent, 0) }}%</strong>
        </a>

        <a href="{{ route('workouts.index') }}" class="topbar-status-pill {{ $topbarWorkoutDone ? 'is-done' : 'is-pending' }}">
            <i class="bi {{ $topbarWorkoutDone ? 'bi-check2-circle' : 'bi-activity' }}"></i>
            <span>Treino</span>
            <strong>{{ $topbarWorkoutDone ? 'feito' : 'pendente' }}</strong>
        </a>
    </div>

    <div class="topbar-right">
        <div class="dropdown">
            <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ações rápidas">
                <i class="bi bi-plus-lg"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end topbar-quick-menu">
                <a class="dropdown-item" href="{{ route('nutrition.index') }}">
                    <i class="bi bi-egg-fried"></i>
                    Registrar refeição
                </a>

                <a class="dropdown-item" href="{{ route('water.index') }}">
                    <i class="bi bi-droplet"></i>
                    Registrar água
                </a>

                <a class="dropdown-item" href="{{ route('workouts.index') }}">
                    <i class="bi bi-activity"></i>
                    Montar treino
                </a>
            </div>
        </div>

        @include('partials.notification-bell-clean')

        <a href="{{ route('settings') }}"
           class="topbar-icon-btn {{ request()->routeIs('settings') ? 'active' : '' }}"
           aria-label="Configurações">
            <i class="bi bi-gear"></i>
        </a>

        <div class="topbar-user">
            <div class="topbar-avatar">
                {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>

            <div>
                <strong>{{ auth()->user()->name ?? 'Usuário' }}</strong>
                <span>BodyTrack</span>
            </div>
        </div>
    </div>
</header>
