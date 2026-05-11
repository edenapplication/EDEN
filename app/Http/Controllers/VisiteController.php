<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visite;
use App\Models\Visiteur;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;

class VisiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Visite::with(['visiteur', 'client', 'grandSite', 'site', 'paiement']);

        if ($request->filled('nom'))
            $query->whereHas('visiteur', fn($q) =>
                $q->where('nom', 'LIKE', "%{$request->nom}%")
                  ->orWhere('numero', 'LIKE', "%{$request->nom}%")
            );

        if ($request->filled('type_personne'))
            $query->where('type_personne', $request->type_personne);

        if ($request->filled('grand_site_id'))
            $query->where('grand_site_id', $request->grand_site_id);

        // ✅ Filtre site
        if ($request->filled('site_id'))
            $query->where('site_id', $request->site_id);

        if ($request->filled('date_debut'))
            $query->where('date_visite', '>=', $request->date_debut);

        if ($request->filled('date_fin'))
            $query->where('date_visite', '<=', $request->date_fin);

        if ($request->filled('mois'))
            $query->whereMonth('date_visite', date('m', strtotime($request->mois . '-01')))
                  ->whereYear('date_visite',  date('Y', strtotime($request->mois . '-01')));

        // ✅ Filtre nombre minimum de visites (ex: au moins N fois)
        if ($request->filled('nb_min') || $request->filled('nb_max')) {
            $nbMin = $request->nb_min ?? 1;
            $nbMax = $request->nb_max ?? 99999;

            // Sous-requête : visiteurs ayant entre nb_min et nb_max visites
            $visiteurIds = Visite::selectRaw('visiteur_id, COUNT(*) as total')
                ->groupBy('visiteur_id')
                ->havingRaw('total >= ? AND total <= ?', [$nbMin, $nbMax])
                ->pluck('visiteur_id');

            $query->whereIn('visiteur_id', $visiteurIds);
        }

        $colonnesDisponibles = [
            'date_visite'   => 'Date',
            'nom'           => 'Nom',
            'numero'        => 'Numéro',
            'type_personne' => 'Type',
            'heure_arrivee' => 'Arrivée',
            'heure_depart'  => 'Départ',
            'grand_site'    => 'Grand Site',
            'site'          => 'Site',
            'note'          => 'Note',
            'nb_visites'    => 'Nb visites',
        ];
        $colonnesChoisies = $request->colonnes ?? array_keys($colonnesDisponibles);

        // Comptage visites par visiteur pour affichage
        $comptageVisites = Visite::selectRaw('visiteur_id, COUNT(*) as total')
            ->groupBy('visiteur_id')
            ->pluck('total', 'visiteur_id');

        $visites    = $query->orderBy('date_visite', 'desc')->orderBy('heure_arrivee', 'desc')->paginate(30)->withQueryString();
        $grandsites = GrandSite::orderBy('nom')->get();
        $sites      = Site::orderBy('name')->get();

        return view('admin.visites.index', compact(
            'visites', 'grandsites', 'sites',
            'colonnesDisponibles', 'colonnesChoisies',
            'comptageVisites'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'visiteur_id'   => 'nullable|exists:visiteurs,id',
            'nom'           => 'required_without:visiteur_id|string',
            'numero'        => 'nullable|string',
            'type_personne' => 'required|in:client,proprietaire,autre',
            'date_visite'   => 'required|date',
            'heure_arrivee' => 'nullable',
            'heure_depart'  => 'nullable',
            'grand_site_id' => 'nullable|exists:grand_sites,id',
            'site_id'       => 'nullable|exists:sites,id',
            'note'          => 'nullable|string',
            'client_id'     => 'nullable|exists:clients,id',
        ]);

        $visiteurId = $request->visiteur_id;
        if (!$visiteurId) {
            $visiteur   = Visiteur::create([
                'nom'    => $request->nom,
                'numero' => $request->numero,
                'type'   => $request->type_personne,
            ]);
            $visiteurId = $visiteur->id;
        }

        $dejaVenu = Visite::where('visiteur_id', $visiteurId)
            ->where('date_visite', $request->date_visite)
            ->exists();

        if ($dejaVenu) {
            return response()->json([
                'success' => false,
                'message' => 'Cette personne a déjà une visite enregistrée pour ce jour.',
            ], 422);
        }

        $visite = Visite::create([
            'visiteur_id'   => $visiteurId,
            'client_id'     => $request->client_id,
            'grand_site_id' => $request->grand_site_id,
            'site_id'       => $request->site_id,
            'date_visite'   => $request->date_visite,
            'heure_arrivee' => $request->heure_arrivee ?: null,
            'heure_depart'  => $request->heure_depart  ?: null,
            'type_personne' => $request->type_personne,
            'note'          => $request->note,
        ]);

        return response()->json(['success' => true, 'visite' => $visite->load('visiteur')]);
    }

    public function update(Request $request, $id)
    {
        $visite = Visite::findOrFail($id);
        $visite->update($request->only([
            'heure_arrivee', 'heure_depart', 'note',
            'grand_site_id', 'site_id', 'type_personne',
        ]));
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Visite::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function visiteurSearch(Request $request)
    {
        $q = $request->get('q', '');
        $visiteurs = Visiteur::where('nom', 'LIKE', "%{$q}%")
            ->orWhere('numero', 'LIKE', "%{$q}%")
            ->limit(10)->get();
        return response()->json($visiteurs);
    }

    // =============================================
    // ✅ IMPORT — accepte dd/mm/yyyy ET yyyy-mm-dd
    // =============================================
    public function import(Request $request)
    {
        $request->validate(['fichier' => 'required|file|mimes:csv,xlsx,xls,txt']);

        $path     = $request->file('fichier')->getRealPath();
        $handle   = fopen($path, 'r');
        $header   = fgetcsv($handle, 0, ';'); // ignorer l'entête
        $imported = 0;
        $errors   = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            try {
                if (count($row) < 2) continue;

                $dateRaw  = trim($row[0] ?? '');
                $nom      = trim($row[1] ?? '');
                $numero   = trim($row[2] ?? '');
                $type     = trim($row[3] ?? 'autre');
                $hArrivee = $this->parseHeure(trim($row[4] ?? ''));
                $hDepart  = $this->parseHeure(trim($row[5] ?? ''));
                $note     = trim($row[8] ?? '');

                if (!$dateRaw || !$nom) continue;

                // ✅ Convertir la date quel que soit le format
                $date = $this->parseDate($dateRaw);
                if (!$date) {
                    $errors[] = "Date invalide : {$dateRaw}";
                    continue;
                }

                $visiteur = Visiteur::firstOrCreate(
                    ['nom' => $nom, 'numero' => $numero ?: ''],
                    ['type' => in_array($type, ['client','proprietaire','autre']) ? $type : 'autre']
                );

                $existe = Visite::where('visiteur_id', $visiteur->id)
                    ->where('date_visite', $date)->exists();

                if (!$existe) {
                    Visite::create([
                        'visiteur_id'   => $visiteur->id,
                        'date_visite'   => $date,
                        'heure_arrivee' => $hArrivee,
                        'heure_depart'  => $hDepart,
                        'type_personne' => in_array($type, ['client','proprietaire','autre']) ? $type : 'autre',
                        'note'          => $note ?: null,
                    ]);
                    $imported++;
                }
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
        fclose($handle);

        $msg = "{$imported} visite(s) importée(s).";
        if ($errors) $msg .= ' Erreurs : ' . implode(' | ', array_slice($errors, 0, 5));

        return back()->with('success', $msg);
    }

    // ✅ Convertit dd/mm/yyyy, d/m/yyyy, yyyy-mm-dd → yyyy-mm-dd
    private function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (!$raw) return null;

        // Format dd/mm/yyyy ou d/m/yyyy
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Format dd-mm-yyyy
        if (preg_match('#^(\d{1,2})-(\d{1,2})-(\d{4})$#', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Format yyyy-mm-dd déjà correct
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $raw)) {
            return $raw;
        }

        // Essai avec strtotime
        $ts = strtotime($raw);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    // ✅ Nettoie l'heure — retire les secondes si présentes
    private function parseHeure(string $raw): ?string
    {
        if (!$raw) return null;
        // HH:MM:SS → HH:MM
        if (preg_match('#^(\d{1,2}):(\d{2})#', $raw, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }
        return null;
    }

    public function export(Request $request)
    {
        $query = Visite::with(['visiteur', 'client', 'grandSite', 'site']);

        if ($request->filled('date_debut'))
            $query->where('date_visite', '>=', $request->date_debut);
        if ($request->filled('date_fin'))
            $query->where('date_visite', '<=', $request->date_fin);
        if ($request->filled('type_personne'))
            $query->where('type_personne', $request->type_personne);
        if ($request->filled('site_id'))
            $query->where('site_id', $request->site_id);

        $visites = $query->orderBy('date_visite', 'desc')->get();

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('admin.visites.pdf', compact('visites'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('visites_' . now()->format('Y-m-d') . '.pdf');
        }

        $filename = 'visites_' . now()->format('Y-m-d') . '.csv';
        $callback = function() use ($visites) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Date','Nom','Numéro','Type','Arrivée','Départ','Grand Site','Site','Note'], ';');
            foreach ($visites as $v) {
                fputcsv($file, [
                    $v->date_visite,
                    $v->visiteur?->nom    ?? '-',
                    $v->visiteur?->numero ?? '-',
                    $v->type_personne,
                    $v->heure_arrivee    ?? '-',
                    $v->heure_depart     ?? '-',
                    $v->grandSite?->nom  ?? '-',
                    $v->site?->name      ?? '-',
                    $v->note             ?? '',
                ], ';');
            }
            fclose($file);
        };
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}