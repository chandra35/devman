<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.users.index', compact('roles'));
    }

    public function data(Request $request)
    {
        $query = User::with('roles');

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();
        $totalData = User::count();

        // Order
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['name', 'username', 'email', 'created_at'];
        $orderBy = $columns[$orderColumn] ?? 'name';
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $users = $query->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => e($user->name),
                'username' => e($user->username),
                'email' => e($user->email ?? '-'),
                'roles' => $user->roles->pluck('name')->map(function ($role) {
                    $colors = [
                        'Super Admin' => 'danger',
                        'Admin' => 'primary',
                        'Operator' => 'info',
                    ];
                    $color = $colors[$role] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . e($role) . '</span>';
                })->implode(' '),
                'is_active' => $user->is_active
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Nonaktif</span>',
                'actions' => $this->getActionButtons($user),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    private function getActionButtons($user)
    {
        $auth = auth()->user();
        $buttons = '';

        if ($auth->can('edit-users')) {
            $buttons .= '<button class="btn btn-xs btn-warning mr-1" onclick="editUser(\'' . $user->id . '\')" title="Edit"><i class="fas fa-edit"></i></button>';
        }

        if ($auth->can('delete-users') && $user->id !== $auth->id) {
            $buttons .= '<button class="btn btn-xs btn-danger" onclick="deleteUser(\'' . $user->id . '\')" title="Hapus"><i class="fas fa-trash"></i></button>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan.',
        ]);
    }

    public function show(User $user)
    {
        $user->load('roles');
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'roles' => 'required|array',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);
        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate.',
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus diri sendiri.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }
}
