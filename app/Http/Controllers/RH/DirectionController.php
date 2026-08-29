<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Direction;
use App\Models\RH\Service;
use App\Models\RH\Poste;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    public function index()
    {
        // ✅ Charger les horaires avec les services
        $directions = Direction::with(['services' => function($query) {
            $query->with('horaires');
        }, 'postes'])->orderBy('nom')->get();
        
        return view('rh.directions.index', compact('directions'));
    }

    // DIRECTIONS
    public function storeDirection(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:rh_directions,code',
            'nom'  => 'required|string|max:100',
        ]);
        Direction::create($request->only('code', 'nom', 'description'));
        return back()->with('success', 'Direction créée');
    }

    public function updateDirection(Request $request, $id)
    {
        $direction = Direction::findOrFail($id);
        $direction->update($request->only('code', 'nom', 'description'));
        return back()->with('success', 'Direction mise à jour');
    }

    public function destroyDirection($id)
    {
        Direction::findOrFail($id)->delete();
        return back()->with('success', 'Direction supprimée');
    }

    // SERVICES
    public function storeService(Request $request)
    {
        $request->validate([
            'direction_id' => 'required|exists:rh_directions,id',
            'nom'          => 'required|string|max:100',
        ]);
        Service::create($request->only('direction_id', 'nom', 'description'));
        return back()->with('success', 'Service créé');
    }

    public function updateService(Request $request, $id)
    {
        Service::findOrFail($id)->update($request->only('direction_id', 'nom', 'description'));
        return back()->with('success', 'Service mis à jour');
    }

    public function destroyService($id)
    {
        Service::findOrFail($id)->delete();
        return back()->with('success', 'Service supprimé');
    }

    // POSTES
    public function storePoste(Request $request)
    {
        $request->validate([
            'code'     => 'required|string|max:30|unique:rh_postes,code',
            'intitule' => 'required|string|max:100',
        ]);
        Poste::create($request->only('direction_id', 'service_id', 'code', 'intitule'));
        return back()->with('success', 'Poste créé');
    }

    public function destroyPoste($id)
    {
        Poste::findOrFail($id)->delete();
        return back()->with('success', 'Poste supprimé');
    }
}