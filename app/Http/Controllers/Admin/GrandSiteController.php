<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrandSite;
use Illuminate\Http\Request;

class GrandSiteController extends Controller
{
    public function index()
    {
        $grandsites = GrandSite::withCount('sites')->latest()->get();
        return view('admin.grand_sites.index', compact('grandsites'));
    }

    public function create()
    {
        return view('admin.grand_sites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        GrandSite::create($request->only('nom', 'description'));

        return redirect()->route('grand-sites.index')->with('success', 'Grand site créé');
    }

    public function edit($id)
    {
        $grandsite = GrandSite::findOrFail($id);
        return view('admin.grand_sites.edit', compact('grandsite'));
    }

    public function update(Request $request, $id)
    {
        $grandsite = GrandSite::findOrFail($id);

        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $grandsite->update($request->only('nom', 'description'));

        return redirect()->route('grand-sites.index')->with('success', 'Modifié');
    }

    public function destroy($id)
    {
        GrandSite::findOrFail($id)->delete();
        return back()->with('success', 'Supprimé');
    }
}