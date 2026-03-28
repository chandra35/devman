<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles.index', compact('permissions'));
    }

    public function data(Request $request)
    {
        $query = Role::withCount('permissions');

        if ($search = $request->input('search.value')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $totalFiltered = $query->count();
        $totalData = Role::count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['name', 'permissions_count', 'created_at'];
        $orderBy = $columns[$orderColumn] ?? 'name';
        $query->orderBy($orderBy, $orderDir);

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $roles = $query->get();

        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => e($role->name),
                'permissions_count' => '<span class="badge badge-info">' . $role->permissions_count . ' permissions</span>',
                'created_at' => $role->created_at->format('d/m/Y H:i'),
                'actions' => $this->getActionButtons($role),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    private function getActionButtons($role)
    {
        $auth = auth()->user();
        $buttons = '';

        if ($auth->can('edit-roles')) {
            $buttons .= '<button class="btn btn-xs btn-warning mr-1" onclick="editRole(' . $role->id . ')" title="Edit"><i class="fas fa-edit"></i></button>';
        }

        if ($auth->can('delete-roles') && !in_array($role->name, ['Super Admin'])) {
            $buttons .= '<button class="btn btn-xs btn-danger" onclick="deleteRole(' . $role->id . ')" title="Hapus"><i class="fas fa-trash"></i></button>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil ditambahkan.',
        ]);
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil diupdate.',
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Role Super Admin tidak bisa dihapus.',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus.',
        ]);
    }
}
