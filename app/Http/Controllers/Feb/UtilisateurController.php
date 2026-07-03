<?php
namespace App\Http\Controllers\Feb;
use App\Http\Controllers\Controller;
use App\Models\Feb\Utilisateur;
use App\Models\Feb\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    public function index()
{
    $utilisateurs = Utilisateur::with(['agence','fiches'])->orderBy('nom')->get();
    $agences      = Agence::where('actif', true)->orderBy('nom')->get();
    return view('feb.admin.utilisateurs.index', compact('utilisateurs','agences'));
}
    public function store(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|unique:feb_utilisateurs,identifiant',
            'password'    => 'required|min:4',
            'nom'         => 'required|string',
            'agence_id'   => 'nullable|exists:feb_agences,id',
        ]);
        Utilisateur::create([
            'identifiant' => $request->identifiant,
            'password'    => Hash::make($request->password),
            'nom'         => $request->nom,
            'prenom'      => $request->prenom,
            'poste'       => $request->poste,
            'agence_id'   => $request->agence_id,
        ]);
        return back()->with('success','Utilisateur créé.');
    }
    public function update(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);
        $data = $request->only('nom','prenom','poste','agence_id','actif');
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);
        $user->update($data);
        return back()->with('success','Utilisateur mis à jour.');
    }
    public function toggle($id)
    {
        $u = Utilisateur::findOrFail($id);
        $u->update(['actif' => !$u->actif]);
        return back()->with('success','Statut modifié.');
    }
    public function destroy($id)
    {
        Utilisateur::findOrFail($id)->delete();
        return back()->with('success','Utilisateur supprimé.');
    }
}