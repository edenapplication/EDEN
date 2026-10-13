<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->orderByDesc('created_at');

        // Filtres
        if ($request->filled('user_id'))    $query->where('user_id', $request->user_id);
        if ($request->filled('event'))      $query->where('event', $request->event);
        if ($request->filled('module'))     $query->where('module', $request->module);
        if ($request->filled('severity'))   $query->where('severity', $request->severity);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'LIKE', "%{$q}%")
                    ->orWhere('user_name', 'LIKE', "%{$q}%")
                    ->orWhere('auditable_label', 'LIKE', "%{$q}%")
                    ->orWhere('url', 'LIKE', "%{$q}%");
            });
        }

        if ($request->filled('du')) $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au')) $query->whereDate('created_at', '<=', $request->au);

        $audits = $query->paginate(50)->appends($request->query());

        $stats = [
            'total'        => Audit::count(),
            'aujourdhui'   => Audit::whereDate('created_at', today())->count(),
            'critiques'    => Audit::where('severity', 'critical')->count(),
            'utilisateurs' => Audit::distinct('user_id')->count('user_id'),
        ];

        $users   = User::orderBy('name')->get(['id', 'name']);
        $events  = Audit::distinct()->pluck('event')->sort();
        $modules = Audit::distinct()->whereNotNull('module')->pluck('module')->sort();

        return view('admin.audits.index', compact('audits', 'stats', 'users', 'events', 'modules'));
    }

    public function show(Audit $audit)
    {
        return view('admin.audits.show', compact('audit'));
    }

    public function destroy(Audit $audit)
    {
        $audit->delete();
        return back()->with('success', 'Entrée d\'audit supprimée.');
    }
}