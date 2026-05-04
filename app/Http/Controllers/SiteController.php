<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\GrandSite;

class SiteController extends Controller
{
    public function index($grand_site_id)
    {
        $grandsite = GrandSite::findOrFail($grand_site_id);
        $sites     = Site::where('grand_site_id', $grand_site_id)->with('tfs')->get();
        return view('admin.sites.index', compact('sites', 'grandsite'));
    }

    public function create($grand_site_id)
    {
        $grandsite = GrandSite::findOrFail($grand_site_id);
        return view('admin.sites.create', compact('grandsite'));
    }

    public function store(Request $request, $grand_site_id)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'svg'         => 'nullable|file',
        ]);

        $svgPath = null;

        if ($request->hasFile('svg') && $request->file('svg')->isValid()) {
            $file        = $request->file('svg');
            $filename    = time() . '_' . $file->getClientOriginalName();
            $destination = storage_path('app/public/maps');
            if (!file_exists($destination)) mkdir($destination, 0777, true);
            $file->move($destination, $filename);
            $svgPath = 'storage/maps/' . $filename;
        }

        Site::create([
            'grand_site_id' => $grand_site_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'svg_path'      => $svgPath,
            'is_active'     => true,
        ]);

        return redirect()->route('sites.index', $grand_site_id)->with('success', 'Site ajouté');
    }

    public function show($siteId)
    {
        $site = Site::with('tfs')->findOrFail($siteId);
        return view('admin.sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'svg'         => 'nullable|file',
        ]);

        $svgPath = $site->svg_path;

        if ($request->hasFile('svg') && $request->file('svg')->isValid()) {
            $file        = $request->file('svg');
            $filename    = time() . '_' . $file->getClientOriginalName();
            $destination = storage_path('app/public/maps');
            if (!file_exists($destination)) mkdir($destination, 0777, true);
            $file->move($destination, $filename);
            $svgPath = 'storage/maps/' . $filename;
        }

        $site->update([
            'name'        => $request->name,
            'description' => $request->description,
            'svg_path'    => $svgPath,
        ]);

        return redirect()->route('sites.index', $site->grand_site_id)->with('success', 'Site modifié');
    }

    public function destroy(Site $site)
    {
        $grand_site_id = $site->grand_site_id;

        if ($site->svg_path) {
            $fp = storage_path('app/public/maps/' . basename($site->svg_path));
            if (file_exists($fp)) unlink($fp);
        }

        $site->delete();

        return redirect()->route('sites.index', $grand_site_id)->with('success', 'Site supprimé');
    }
}