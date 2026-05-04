<?php

namespace App\Http\Controllers;

use App\Models\Commercial;
use Illuminate\Http\Request;

class CommercialController extends Controller
{
    // LISTE
    public function index()
    {
        $commerciaux = Commercial::latest()->get();
        return view('admin.commerciaux.index', compact('commerciaux'));
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'agence' => 'nullable|string'
        ]);

        Commercial::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'agence' => $request->agence
        ]);

        return back()->with('success', 'Commercial créé');
    }

    // UPDATE ✅
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'agence' => 'nullable|string'
        ]);

        $commercial = Commercial::findOrFail($id);

        $commercial->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'agence' => $request->agence
        ]);

        return back()->with('success', 'Commercial modifié');
    }

    // DELETE (avec confirmation côté front)
    public function destroy($id)
    {
        Commercial::findOrFail($id)->delete();
        return back()->with('success', 'Commercial supprimé');
    }
}