<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private function validateMasterPassword(Request $request): void
    {
        if ($request->master_password !== env('ADMIN_MASTER_PASSWORD')) {
            abort(403, 'Senha administrativa inválida.');
        }
    }

    private function logAction(string $action, string $description): void
    {
        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }

    public function index()
    {
        $users = User::latest()->paginate(15);

        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalCommonUsers = User::where('is_admin', false)->count();

        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'totalAdmins',
            'totalCommonUsers'
        ));
    }

    public function toggleStatus(Request $request, User $user)
    {
        $this->validateMasterPassword($request);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Você não pode bloquear sua própria conta.');
        }

        $oldStatus = $user->is_active ? 'ativo' : 'bloqueado';

        $user->is_active = ! $user->is_active;
        $user->save();

        $newStatus = $user->is_active ? 'ativo' : 'bloqueado';

        $this->logAction(
            'toggle_status',
            auth()->user()->name .
            " alterou o status de {$user->name} de {$oldStatus} para {$newStatus}."
        );

        return back()->with('success', 'Status alterado com sucesso.');
    }

    public function toggleAdmin(Request $request, User $user)
    {
        $this->validateMasterPassword($request);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Você não pode alterar sua própria permissão.');
        }

        $oldRole = $user->is_admin ? 'administrador' : 'usuário comum';

        $user->is_admin = ! $user->is_admin;
        $user->save();

        $newRole = $user->is_admin ? 'administrador' : 'usuário comum';

        $this->logAction(
            'toggle_admin',
            auth()->user()->name .
            " alterou a permissão de {$user->name} de {$oldRole} para {$newRole}."
        );

        return back()->with('success', 'Permissão alterada com sucesso.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->validateMasterPassword($request);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $deletedUserName = $user->name;
        $deletedUserEmail = $user->email;

        $this->logAction(
            'delete_user',
            auth()->user()->name .
            " excluiu o usuário {$deletedUserName} ({$deletedUserEmail})."
        );

        $user->delete();

        return back()->with('success', 'Usuário excluído com sucesso.');
    }
}
