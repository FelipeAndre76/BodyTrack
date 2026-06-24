<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 25px;
        }

        .meta {
            width: 100%;
            margin-bottom: 18px;
            font-size: 11px;
            color: #4b5563;
        }

        .meta td {
            border: none;
            padding: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #111;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }

        td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        .date-cell {
            white-space: nowrap;
            width: 95px;
        }

        .date-cell strong {
            display: block;
            font-weight: 700;
        }

        .date-cell span {
            display: block;
            color: #6b7280;
            margin-top: 2px;
        }

        .action-badge {
            display: inline-block;
            background: #ecfccb;
            color: #365314;
            border: 1px solid #bef264;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>

<body>

@php
    $actionLabels = [
        'toggle_admin' => 'Alteração de admin',
        'toggle_status' => 'Bloqueio/Desbloqueio',
        'delete_user' => 'Exclusão de usuário',

        'create_exercise' => 'Criação de exercício',
        'update_exercise' => 'Atualização de exercício',
        'delete_exercise' => 'Exclusão de exercício',

        'create_category' => 'Criação de categoria',
        'update_category' => 'Atualização de categoria',
        'delete_category' => 'Exclusão de categoria',

        'upload_photo' => 'Upload de foto',
        'update_photo' => 'Atualização de foto',
        'delete_photo' => 'Exclusão de foto',
    ];
@endphp

<h1>BodyTrack</h1>

<div class="subtitle">
    Relatório de Logs Administrativos
</div>

<table class="meta">
    <tr>
        <td>
            <strong>Total de registros:</strong> {{ $logs->count() }}
        </td>
        <td style="text-align: right;">
            <strong>Gerado em:</strong>
            {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Administrador</th>
            <th>Ação</th>
            <th>Descrição</th>
        </tr>
    </thead>

    <tbody>
        @foreach($logs as $log)
            <tr>
                <td class="date-cell">
                    <strong>
                        {{ $log->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}
                    </strong>

                    <span>
                        {{ $log->created_at->timezone(config('app.timezone'))->format('H:i') }}
                    </span>
                </td>

                <td>
                    {{ $log->admin->name ?? 'Admin removido' }}
                </td>

                <td>
                    <span class="action-badge">
                        {{ $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action)) }}
                    </span>
                </td>

                <td>
                    {{ $log->description }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    BodyTrack - Relatório administrativo
</div>

</body>
</html>
