<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Evolucao - BodyTrack</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: #f5f7f2;
            color: #111827;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }
        .page { padding: 28px; }
        .hero {
            background: #0b0f0c;
            color: #fff;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 18px;
        }
        .hero h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }
        .hero p {
            margin: 0;
            color: #cbd5c0;
        }
        .brand {
            color: #a3e635;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .grid {
            width: 100%;
            border-spacing: 10px;
            margin: 0 -10px 14px;
        }
        .card {
            background: #fff;
            border: 1px solid #dce7cf;
            border-radius: 8px;
            padding: 14px;
            vertical-align: top;
        }
        .card span, .muted {
            color: #64748b;
            font-size: 11px;
        }
        .card strong {
            display: block;
            color: #111827;
            font-size: 20px;
            margin-top: 6px;
        }
        h2 {
            margin: 18px 0 10px;
            font-size: 18px;
            color: #111827;
        }
        .section {
            background: #fff;
            border: 1px solid #dce7cf;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 14px;
        }
        .progress {
            height: 8px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px;
        }
        .bar {
            height: 8px;
            background: #84cc16;
            border-radius: 999px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th,
        table.data td {
            border-bottom: 1px solid #e5e7eb;
            padding: 9px 6px;
            text-align: left;
        }
        table.data th {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
        }
        .photos {
            width: 100%;
            border-spacing: 12px;
            margin: 0 -12px;
        }
        .photo-card {
            width: 50%;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            vertical-align: top;
        }
        .photo-card img {
            width: 100%;
            max-height: 270px;
            object-fit: cover;
            border-radius: 6px;
            margin: 8px 0;
        }
        .footer {
            color: #64748b;
            text-align: center;
            font-size: 10px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
@php
    $proteinPercent = min(100, ($report['nutrition_averages']['protein'] / max(1, $nutritionGoals['protein'])) * 100);
    $caloriesPercent = min(100, ($report['nutrition_averages']['calories'] / max(1, $nutritionGoals['calories'])) * 100);
    $waterPercent = min(100, ($report['water_average'] / max(1, $metrics['water_goal'])) * 100);

    $checkInPhotoSrc = function ($checkIn) {
        if (!$checkIn || !$checkIn->photo_data) {
            return null;
        }

        $data = is_resource($checkIn->photo_data)
            ? stream_get_contents($checkIn->photo_data)
            : $checkIn->photo_data;

        return 'data:' . ($checkIn->photo_mime ?: 'image/jpeg') . ';base64,' . base64_encode($data);
    };

    $firstPhotoSrc = $checkInPhotoSrc($firstPhoto);
    $latestPhotoSrc = $checkInPhotoSrc($latestPhoto);
@endphp

<div class="page">
    <div class="hero">
        <div class="brand">BodyTrack</div>
        <h1>Relatorio de Evolucao</h1>
        <p>{{ $user->name }} · Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="grid">
        <tr>
            <td class="card">
                <span>Peso atual</span>
                <strong>{{ number_format((float) $metrics['weight'], 1, ',', '.') }}kg</strong>
                <span>Meta {{ number_format((float) $metrics['goal_weight'], 1, ',', '.') }}kg</span>
            </td>
            <td class="card">
                <span>IMC</span>
                <strong>{{ number_format((float) $metrics['bmi'], 1, ',', '.') }}</strong>
                <span>{{ $metrics['bmi_category']['label'] }}</span>
            </td>
            <td class="card">
                <span>Progresso</span>
                <strong>{{ number_format((float) $metrics['progress_percent'], 0, ',', '.') }}%</strong>
                <span>{{ number_format((float) $metrics['weight_lost'], 1, ',', '.') }}kg eliminados</span>
            </td>
        </tr>
    </table>

    <h2>Consistencia dos ultimos 7 dias</h2>
    <table class="grid">
        <tr>
            <td class="card">
                <span>Proteina media</span>
                <strong>{{ number_format((float) $report['nutrition_averages']['protein'], 1, ',', '.') }}g</strong>
                <div class="progress"><div class="bar" style="width: {{ $proteinPercent }}%"></div></div>
            </td>
            <td class="card">
                <span>Calorias media</span>
                <strong>{{ number_format((float) $report['nutrition_averages']['calories'], 0, ',', '.') }}</strong>
                <div class="progress"><div class="bar" style="width: {{ $caloriesPercent }}%"></div></div>
            </td>
            <td class="card">
                <span>Agua media</span>
                <strong>{{ number_format((float) $report['water_average'], 0, ',', '.') }}ml</strong>
                <div class="progress"><div class="bar" style="width: {{ $waterPercent }}%"></div></div>
            </td>
            <td class="card">
                <span>Treinos</span>
                <strong>{{ $report['workouts_count'] }}</strong>
                <span>Ultimos 7 dias</span>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2 style="margin-top:0">Evolucao de peso</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Peso</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['weight_points'] as $log)
                    <tr>
                        <td>{{ $log->recorded_at->format('d/m/Y') }}</td>
                        <td>{{ number_format((float) $log->weight, 1, ',', '.') }}kg</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Nenhuma pesagem registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 style="margin-top:0">Ultimos check-ins</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Peso</th>
                    <th>Energia</th>
                    <th>Humor</th>
                    <th>Sono</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checkIns as $checkIn)
                    <tr>
                        <td>{{ $checkIn->check_in_date->format('d/m/Y') }}</td>
                        <td>{{ $checkIn->weight ? number_format((float) $checkIn->weight, 1, ',', '.') . 'kg' : '-' }}</td>
                        <td>{{ $checkIn->energy_level ?? '-' }}/5</td>
                        <td>{{ $checkIn->mood_level ?? '-' }}/5</td>
                        <td>{{ $checkIn->sleep_quality ?? '-' }}/5</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Nenhum check-in registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($firstPhotoSrc || $latestPhotoSrc)
        <div class="section">
            <h2 style="margin-top:0">Fotos de evolucao</h2>
            <table class="photos">
                <tr>
                    @if($firstPhotoSrc)
                        <td class="photo-card">
                            <span>Primeira foto</span>
                            <img src="{{ $firstPhotoSrc }}" alt="Primeira foto">
                            <strong>{{ $firstPhoto->check_in_date->format('d/m/Y') }}</strong>
                        </td>
                    @endif

                    @if($latestPhotoSrc)
                        <td class="photo-card">
                            <span>Foto mais recente</span>
                            <img src="{{ $latestPhotoSrc }}" alt="Foto mais recente">
                            <strong>{{ $latestPhoto->check_in_date->format('d/m/Y') }}</strong>
                            @if($weightDiff !== null)
                                <div class="muted">
                                    Diferença: {{ $weightDiff <= 0 ? '-' : '+' }}{{ number_format(abs($weightDiff), 1, ',', '.') }}kg
                                </div>
                            @endif
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        Relatorio gerado automaticamente pelo BodyTrack. Os dados sao informativos e nao substituem acompanhamento profissional.
    </div>
</div>
</body>
</html>
