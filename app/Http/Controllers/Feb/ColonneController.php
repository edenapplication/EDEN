<?php
namespace App\Http\Controllers\Feb;
use App\Http\Controllers\Controller;
use App\Models\Feb\Colonne;
use Illuminate\Http\Request;

class ColonneController extends Controller
{
    public function index()
    {
        $colonnes = Colonne::orderBy('ordre')->get();
        return view('feb.admin.colonnes.index', compact('colonnes'));
    }
    public function store(Request $request)
    {
        $request->validate(['libelle'=>'required|string|max:100']);
        Colonne::create(['libelle'=>$request->libelle,'description'=>$request->description,'ordre'=>Colonne::max('ordre')+1]);
        return back()->with('success','Colonne créée.');
    }
    public function update(Request $request, $id)
    {
        Colonne::findOrFail($id)->update($request->only('libelle','description','ordre','actif'));
        return back()->with('success','Colonne mise à jour.');
    }
    public function destroy($id)
    {
        Colonne::findOrFail($id)->delete();
        return back()->with('success','Colonne supprimée.');
    }
}