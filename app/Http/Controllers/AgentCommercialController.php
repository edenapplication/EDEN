<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgentCommercial;

class AgentCommercialController extends Controller
{
    public function index()
    {
        $agents = AgentCommercial::latest()->get();
        return view('admin.agents.index', compact('agents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'    => 'required|string|max:255',
            'numero' => 'nullable|string|max:50',
        ]);

        AgentCommercial::create($request->only('nom', 'numero'));
        return back()->with('success', 'Agent créé');
    }

    public function update(Request $request, $id)
    {
        $agent = AgentCommercial::findOrFail($id);
        $request->validate([
            'nom'    => 'required|string|max:255',
            'numero' => 'nullable|string|max:50',
        ]);
        $agent->update($request->only('nom', 'numero'));
        return back()->with('success', 'Agent modifié');
    }

    public function destroy($id)
    {
        AgentCommercial::findOrFail($id)->delete();
        return back()->with('success', 'Supprimé');
    }
}