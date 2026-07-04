<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    public function modifierNom(Request $request, $clientId)
{
    $request->validate(['nom' => 'required|string|max:150']);
    $client = \App\Models\Client::findOrFail($clientId);
    $client->update(['name' => $request->nom]);
    return response()->json(['success' => true, 'nom' => $client->name]);
}

}