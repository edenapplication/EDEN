<?php
namespace App\Http\Controllers\Feb;

use App\Http\Controllers\Controller;
use App\Models\Feb\Agence;
use Illuminate\Http\Request;

class AgenceController extends Controller
{
    public function index()
{
    $agences = Agence::withCount('utilisateurs')->orderBy('nom')->get();
    return view('feb.admin.agences.index', compact('agences'));
}
    public function store(Request $request)
    {
        $request->validate(['nom'=>'required|string|max:100','code'=>'nullable|string|max:20','localite'=>'nullable|string']);
        Agence::create($request->only('nom','code','localite'));
        return back()->with('success','Agence créée.');
    }
    public function update(Request $request, $id)
    {
        Agence::findOrFail($id)->update($request->only('nom','code','localite','actif'));
        return back()->with('success','Agence mise à jour.');
    }
    public function destroy($id)
    {
        Agence::findOrFail($id)->delete();
        return back()->with('success','Agence supprimée.');
    }
}