<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions.index');
    }

    public function data(Request $request)
    {
        $query = Permission::query();

        if ($search = $request->input('search.value')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $totalFiltered = $query->count();
        $totalData = Permission::count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['name', 'guard_name', 'created_at'];
        $orderBy = $columns[$orderColumn] ?? 'name';
        $query->orderBy($orderBy, $orderDir);

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $permissions = $query->get();

        $data = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => e($permission->name),
                'guard_name' => e($permission->guard_name),
                'created_at' => $permission->created_at->format('d/m/Y H:i'),
                'actions' => $this->getActionButtons($permission),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    private function getActionButtons($permission)
    {
        $auth = auth()->user();
        $buttons = '';

        if ($auth->can('edit-permissions')) {
            $buttons .= '<button class="btn btn-xs btn-warning mr-1" onclick="editPermission(' . $permission->id . ')" title="Edit"><i class="fas fa-edit"></i></button>';
        }

        if ($auth->can('delete-permissions')) {
            $buttons .= '<button class="btn btn-xs btn-danger" onclick="deletePermission(' . $permission->id . ')" title="Hapus"><i class="fas fa-trash"></i></button>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil ditambahkan.',
        ]);
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'data' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil diupdate.',
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil dihapus.',
        ]);
    }

    public function scan()
    {
        $routes = Route::getRoutes();
        $scannedPermissions = [];

        foreach ($routes as $route) {
            $middleware = $route->middleware();
            foreach ($middleware as $mw) {
                if (str_starts_with($mw, 'permission:')) {
                    $perms = explode('|', str_replace('permission:', '', $mw));
                    foreach ($perms as $perm) {
                        $scannedPermissions[] = trim($perm);
                    }
                }
            }
        }

        $scannedPermissions = array_unique($scannedPermissions);
        $existingPermissions = Permission::pluck('name')->toArray();
        $newPermissions = array_diff($scannedPermissions, $existingPermissions);

        $created = 0;
        foreach ($newPermissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => "Scan selesai. {$created} permission baru ditemukan dan ditambahkan.",
            'new_permissions' => array_values($newPermissions),
            'total_scanned' => count($scannedPermissions),
        ]);
    }
}
