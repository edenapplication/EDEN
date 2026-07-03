<?php
namespace App\Http\Controllers\Feb;

use App\Http\Controllers\Controller;
use App\Models\Feb\Utilisateur;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('feb.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required',
            'password'    => 'required',
        ]);

        $user = Utilisateur::where('identifiant', $request->identifiant)
                           ->where('actif', true)
                           ->first();

        if (!$user || !$user->verifierPassword($request->password)) {
            return back()->withErrors(['identifiant' => 'Identifiant ou mot de passe incorrect.']);
        }

        session(['feb_user_id' => $user->id]);
        return redirect()->route('feb.fiches.index');
    }

    public function logout()
    {
        session()->forget('feb_user_id');
        return redirect()->route('feb.login');
    }
}