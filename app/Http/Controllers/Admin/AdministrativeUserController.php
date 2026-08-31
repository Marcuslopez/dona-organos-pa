<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdministrativeUserRequest;
use App\Http\Requests\Admin\UpdateAdministrativeUserRequest;
use App\Mail\AdministrativeTemporaryPasswordMail;
use App\Models\User;
use App\Services\AdministrativeUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdministrativeUserController extends Controller
{
    public function __construct(private readonly AdministrativeUserService $service) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('buscar', ''));
        $status = (string) $request->query('estado', '');
        $role = (string) $request->query('rol', '');

        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"),
            ))
            ->when(in_array($role, ['master', 'administrator'], true), fn ($query) => $query->where('role', $role))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'status', 'role'));
    }

    public function store(StoreAdministrativeUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $this->service->create($data, $request->user(), $request);
        Mail::to($user)->send(new AdministrativeTemporaryPasswordMail($user, $data['password']));

        return redirect()->route('admin.users.index')->with('status', 'Usuario administrativo creado y contraseña temporal enviada al correo indicado.');
    }

    public function update(UpdateAdministrativeUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $user = $this->service->update($user, $data, $request->user(), $request);

        if ($data['reset_password']) {
            Mail::to($user)->send(new AdministrativeTemporaryPasswordMail($user, $data['password']));
        }

        $status = 'Usuario administrativo actualizado correctamente.';
        if ($data['reset_password']) $status .= ' Se generó y envió una contraseña temporal al correo indicado.';
        if ($data['unlock_access']) $status .= ' Se solicitó el desbloqueo de acceso.';

        return redirect()->route('admin.users.index')->with('status', $status);
    }
}
