<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;

class AdminLogController extends Controller
{
    public function index()
    {
        $logs = AdminLog::with('admin')
            ->latest()
            ->paginate(20);

        return view(
            'admin.logs.index',
            compact('logs')
        );
    }

    public function export()
    {
        $logs = AdminLog::with('admin')
            ->latest()
            ->get();

        $fileName =
            'relatorio_logs_admin_' .
            now()->format('Y_m_d_H_i') .
            '.csv';

        $headers = [
            'Content-Type' =>
                'text/csv; charset=UTF-8',

            'Content-Disposition' =>
                "attachment; filename={$fileName}",
        ];

        return response()->streamDownload(
            function () use ($logs) {

                echo "\xEF\xBB\xBF";

                $file = fopen('php://output', 'w');

                fputcsv(
                    $file,
                    [
                        'Data',
                        'Administrador',
                        'Ação',
                        'Descrição'
                    ],
                    ';'
                );

                foreach ($logs as $log) {

                    fputcsv(
                        $file,
                        [
                            $log->created_at->format('d/m/Y H:i'),
                            $log->admin->name ?? 'Admin removido',
                            $log->action,
                            $log->description,
                        ],
                        ';'
                    );
                }

                fclose($file);

            },
            $fileName,
            $headers
        );
    }
}
