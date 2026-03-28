<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PusakaUser;
use App\Services\KemenagNipService;
use Illuminate\Http\Request;

class PusakaUserController extends Controller
{
    public function index()
    {
        return view('admin.pusaka-users.index');
    }

    public function data(Request $request)
    {
        $query = PusakaUser::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();
        $totalData = PusakaUser::count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['nip', 'name', 'is_active', 'created_at'];
        $orderBy = $columns[$orderColumn] ?? 'nip';
        $query->orderBy($orderBy, $orderDir);

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $users = $query->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'nip' => e($user->nip),
                'name' => e($user->name),
                'is_active' => $user->is_active
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Nonaktif</span>',
                'notes' => e($user->notes ?? '-'),
                'created_at' => $user->created_at->format('d/m/Y H:i'),
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
        $buttons = '<button class="btn btn-xs btn-warning mr-1" onclick="editPusakaUser(\'' . $user->id . '\')" title="Edit"><i class="fas fa-edit"></i></button>';
        $buttons .= '<button class="btn btn-xs btn-danger" onclick="deletePusakaUser(\'' . $user->id . '\')" title="Hapus"><i class="fas fa-trash"></i></button>';
        return $buttons;
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:30|unique:pusaka_users',
            'name' => 'required|string|max:255',
        ]);

        PusakaUser::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'satker' => $request->satker,
            'golongan' => $request->golongan,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->notes,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User Pusaka berhasil ditambahkan.',
        ]);
    }

    public function show(PusakaUser $pusakaUser)
    {
        return response()->json([
            'success' => true,
            'data' => $pusakaUser,
        ]);
    }

    public function update(Request $request, PusakaUser $pusakaUser)
    {
        $request->validate([
            'nip' => 'required|string|max:30|unique:pusaka_users,nip,' . $pusakaUser->id,
            'name' => 'required|string|max:255',
        ]);

        $pusakaUser->update([
            'nip' => $request->nip,
            'name' => $request->name,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'satker' => $request->satker,
            'golongan' => $request->golongan,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->notes,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User Pusaka berhasil diupdate.',
        ]);
    }

    public function destroy(PusakaUser $pusakaUser)
    {
        $pusakaUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'User Pusaka berhasil dihapus.',
        ]);
    }

    public function checkNip(Request $request, KemenagNipService $nipService)
    {
        $request->validate(['nip' => 'required|string|size:18']);

        $result = $nipService->cekNip($request->nip);

        return response()->json($result);
    }
}
