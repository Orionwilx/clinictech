<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Listado de usuarios (incluye desactivados; los eliminados con withTrashed).
     */
    public function index(): View
    {
        $this->authorize('view users');

        $users = User::with('roles')
            ->withTrashed()
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create users');

        return view('admin.users.create', ['roles' => Role::pluck('name')]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.users.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function show(User $user): View
    {
        $this->authorize('view users');

        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update users');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::pluck('name'),
            'currentRole' => $user->roles->first()?->name,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    /**
     * Baja lógica (soft delete). Recuperable desde restore().
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete users');

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Usuario eliminado (recuperable).');
    }

    /**
     * Recupera un usuario eliminado (soft delete).
     */
    public function restore(int $id): RedirectResponse
    {
        $this->authorize('delete users');

        User::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.users.index')
            ->with('status', 'Usuario recuperado correctamente.');
    }
}
