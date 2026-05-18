<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,rh,commercial',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'actif'    => true,
        ]);

        return back()->with('success', 'Utilisateur créé');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only('name', 'email', 'role', 'actif');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        return back()->with('success', 'Utilisateur mis à jour');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé');
    }

    public function toggleActif($id)
    {
        $user = User::findOrFail($id);
        $user->update(['actif' => !$user->actif]);
        return back()->with('success', $user->actif ? 'Compte activé' : 'Compte désactivé');
    }
}