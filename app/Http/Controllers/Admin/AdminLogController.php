<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    private array $availableActions = [
        'toggle_admin' => 'Alteração de Admin',
        'toggle_status' => 'Bloqueio/Desbloqueio',
        'delete_user' => 'Exclusão de Usuário',

        'create_exercise' => 'Criação de Exercício',
        'update_exercise' => 'Atualização de Exercício',
        'delete_exercise' => 'Exclusão de Exercício',

        'create_category' => 'Criação de Categoria',
        'update_category' => 'Atualização de Categoria',
        'delete_category' => 'Exclusão de Categoria',

        'upload_photo' => 'Upload de Foto',
        'update_photo' => 'Atualização de Foto',
        'delete_photo' => 'Exclusão de Foto',
    ];

    private function actionLabel(string $action): string
    {
        return $this->availableActions[$action]
            ?? ucfirst(str_replace('_', ' ', $action));
    }

    public function index(Request $request)
    {
        $selectedActions = $request->input('actions', []);

        $logsQuery = AdminLog::with('admin')->latest();

        if (!empty($selectedActions)) {
            $logsQuery->whereIn('action', $selectedActions);
        }

        $totalLogs = AdminLog::count();

        $logsLast30Days = AdminLog::where('created_at', '>=', now()->subDays(30))
            ->count();

        $photoLogs = AdminLog::whereIn('action', [
            'upload_photo',
            'update_photo',
            'delete_photo',
        ])->count();

        $userLogs = AdminLog::whereIn('action', [
            'toggle_admin',
            'toggle_status',
            'delete_user',
        ])->count();

        $logs = $logsQuery
            ->paginate(12)
            ->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'availableActions' => $this->availableActions,
            'selectedActions' => $selectedActions,
            'totalLogs' => $totalLogs,
            'logsLast30Days' => $logsLast30Days,
            'photoLogs' => $photoLogs,
            'userLogs' => $userLogs,
        ]);
    }

    public function export(Request $request)
    {
        $selectedActions = $request->input('actions', []);

        $logsQuery = AdminLog::with('admin')->latest();

        if (!empty($selectedActions)) {
            $logsQuery->whereIn('action', $selectedActions);
        }

        $logs = $logsQuery->get();

        $fileName = 'relatorio_logs_admin_' . now()->format('Y_m_d_H_i') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        return response()->streamDownload(function () use ($logs) {
            echo "\xEF\xBB\xBF";

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Data',
                'Administrador',
                'Ação',
                'Descrição',
            ], ';');

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at
                        ->timezone(config('app.timezone'))
                        ->format('d/m/Y H:i'),

                    $log->admin->name ?? 'Admin removido',

                    $this->actionLabel($log->action),

                    $log->description,
                ], ';');
            }

            fclose($file);
        }, $fileName, $headers);
    }

    public function pdf(Request $request)
    {
        $selectedActions = $request->input('actions', []);

        $logsQuery = AdminLog::with('admin')->latest();

        if (!empty($selectedActions)) {
            $logsQuery->whereIn('action', $selectedActions);
        }

        $logs = $logsQuery->get();

        $pdf = Pdf::loadView('admin.logs.pdf', [
            'logs' => $logs,
            'selectedActions' => $selectedActions,
            'availableActions' => $this->availableActions,
        ]);

        return $pdf->download(
            'bodytrack_logs_' . now()->format('Y_m_d_H_i') . '.pdf'
        );
    }

    public function filter(Request $request)
    {
        $selectedActions = $request->input('actions', []);

        $logsQuery = AdminLog::with('admin')->latest();

        if (!empty($selectedActions)) {
            $logsQuery->whereIn('action', $selectedActions);
        }

        $logs = $logsQuery
            ->paginate(12)
            ->withQueryString();

        return view('admin.logs.partials.list', compact('logs'))->render();
    }
}
