<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tf;
use Illuminate\Support\Facades\Storage;

class TfController extends Controller
{
    // 💾 création TF depuis carte
    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|integer',
            'svg_zone_id' => 'required|string',
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $filePath = null;

        // 🎨 couleur (toujours générée)
        $color = $request->color ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF));

        // 📁 upload fichier TF
        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $file = $request->file('file');

            $fileName = time().'_'.$file->getClientOriginalName();

            // ✅ stockage propre Laravel (DISK public)
            $filePath = $file->storeAs('tfs', $fileName, 'public');
        }

        $tf = Tf::create([
            'site_id' => $request->site_id,
            'svg_zone_id' => $request->svg_zone_id,
            'title' => $request->title,
            'file_path' => $filePath, // ex: tfs/xxx.pdf
            'color' => $color,
            'status' => 'available',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'tf' => $tf
        ]);
    }

    // 📄 afficher TF
    public function show(Tf $tf)
    {
        return view('admin.tf.show', compact('tf'));
    }

    public function update(Request $request, Tf $tf)
{
    $request->validate([
        'title' => 'required|string|max:255',
    ]);

    $filePath = $tf->file_path;

    // 📁 si nouveau fichier
    if ($request->hasFile('file') && $request->file('file')->isValid()) {

        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();

        $filePath = $file->storeAs('tfs', $fileName, 'public');
    }

    $tf->update([
        'title' => $request->title,
        'file_path' => $filePath,
    ]);

    return response()->json([
        'success' => true
    ]);
}
}