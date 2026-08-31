<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Retard;
use App\Models\RH\Employe;
use App\Models\RH\Direction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\RH\Absence;
use Carbon\Carbon;

class RetardController extends Controller
{
    // Horaires de référence
    const HEURE_DEBUT  = '08:00'; // Début de journée
    const HEURE_FIN    = '18:00'; // Fin de journée normale

    public function index(Request $request)
    {
        $mois       = $request->input('mois', now()->format('Y-m'));
        $dirId      = $request->input('direction_id');
        $empId      = $request->input('employe_id');
        $directions = Direction::orderBy('nom')->get();
        $employes   = Employe::where('actif', true)->orderBy('nom')->get();

        $isSQLite = config('database.default') === 'sqlite';

        $query = Retard::with('employe.direction', 'employe.service');
        if ($isSQLite) {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$mois]);
        } else {
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$mois]);
        }

        if ($dirId) {
            $query->whereHas('employe', fn($q) => $q->where('direction_id', $dirId));
        }
        if ($empId) {
            $query->where('employe_id', $empId);
        }

        $retards = $query->orderByDesc('date')->get();

        // Grouper par employé — total du mois
        $parEmploye = $retards->groupBy('employe_id')->map(function ($lignes) {
            $emp = $lignes->first()->employe;
            return [
                'employe'        => $emp,
                'nb'             => $lignes->count(),
                'duree_min'      => $lignes->sum('minutes_retard'),
                'heures_sup_min' => $lignes->sum('minutes_sup'),
                'lignes'         => $lignes,
            ];
        })->sortByDesc('nb')->values();

        // Stats par direction
        $parDirection = $retards->groupBy('employe.direction.nom')->map(fn($g) => [
            'nb'       => $g->count(),
            'employes' => $g->groupBy('employe_id')->map(fn($ge) => [
                'nom'     => $ge->first()->employe?->nom . ' ' . $ge->first()->employe?->prenom,
                'nb'      => $ge->count(),
                'service' => $ge->first()->employe?->service?->nom ?? '-',
            ])->sortByDesc('nb')->values(),
        ])->sortByDesc('nb');

        $parService = $retards->groupBy('employe.service.nom')->map(fn($g) => [
            'nb' => $g->count(),
        ])->sortByDesc('nb');

        return view('rh.retards.index', compact(
            'retards', 'parEmploye', 'parDirection', 'parService',
            'employes', 'directions', 'mois', 'dirId', 'empId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'date'       => 'required|date',
        ]);

        $data = $request->all();

        // Calcul automatique si heure arrivée fournie
        if (!empty($data['heure_arrivee'])) {
            [$retard, $sup] = $this->calculerTemps($data['heure_arrivee'], $data['heure_depart'] ?? null);
            $data['minutes_retard'] = $retard;
            $data['minutes_sup']    = $sup;
        }

        Retard::create($data);
        return back()->with('success', 'Retard enregistré');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        if (!empty($data['heure_arrivee'])) {
            [$retard, $sup] = $this->calculerTemps($data['heure_arrivee'], $data['heure_depart'] ?? null);
            $data['minutes_retard'] = $retard;
            $data['minutes_sup']    = $sup;
        }

        Retard::findOrFail($id)->update($data);
        return back()->with('success', 'Retard mis à jour');
    }

    public function destroy($id)
    {
        Retard::findOrFail($id)->delete();
        return redirect()->route('rh.retards.index')->with('success', 'Retard supprimé');
    }

    // =========================================================
    // Calcul retard et heures sup
    // H.A = heure d'arrivée réelle
    // H.D = heure de départ réelle
    // Début référence = 08:00 | Fin référence = 18:00
    // =========================================================
    private function calculerTemps(string $heureArrivee, ?string $heureDepart): array
    {
        $minutesRetard = 0;
        $minutesSup    = 0;

        try {
            $debut     = Carbon::createFromFormat('H:i', self::HEURE_DEBUT);
            $fin       = Carbon::createFromFormat('H:i', self::HEURE_FIN);
            $arrivee   = Carbon::createFromFormat('H:i', $heureArrivee);

            // Retard = si arrivée après 08:00
            if ($arrivee->gt($debut)) {
                $minutesRetard = (int) $debut->diffInMinutes($arrivee);
            }

            // Heures sup = si départ après 18:00
            if ($heureDepart) {
                $depart = Carbon::createFromFormat('H:i', $heureDepart);
                if ($depart->gt($fin)) {
                    $minutesSup = (int) $fin->diffInMinutes($depart);
                }
            }
        } catch (\Throwable $e) {
            // Heures mal formatées — on laisse à 0
        }

        return [$minutesRetard, $minutesSup];
    }

    // =========================================================
    // IMPORT EXCEL
    // Colonnes : A=Matricule | B=NOMS | C=Date | D=H.A | E=H.D
    // =========================================================
   public function importExcel(Request $request)
{
    $request->validate([
        'fichier_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);

    $spreadsheet = IOFactory::load($request->file('fichier_excel')->getRealPath());
    $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    $inseres = 0;
    $ignores = 0;
    $absencesCrees = 0;
    $erreurs = [];
    $first = true;

    // ✅ Tableau pour suivre les employés + dates déjà traités
    $traites = [];

    foreach ($rows as $row) {
        if ($first) { $first = false; continue; }

        $matricule = trim($row['A'] ?? '');
        $dateRaw = trim($row['C'] ?? '');
        $ha = trim($row['D'] ?? '');
        $hd = trim($row['E'] ?? '');

        if (empty($matricule) || empty($dateRaw)) {
            $ignores++;
            continue;
        }

        $employe = Employe::where('matricule', $matricule)->first();
        if (!$employe) {
            $erreurs[] = "Matricule introuvable : {$matricule}";
            continue;
        }

        try {
            if (is_numeric($dateRaw)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateRaw);
                $dateStr = Carbon::instance($date)->format('Y-m-d');
            } else {
                $dateStr = Carbon::parse($dateRaw)->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            $erreurs[] = "Date invalide pour {$matricule} : {$dateRaw}";
            continue;
        }

        // ✅ VÉRIFIER SI DÉJÀ TRAITÉ (même employé + même date)
        $cle = $employe->id . '_' . $dateStr;
        if (isset($traites[$cle])) {
            $ignores++;
            continue;
        }
        $traites[$cle] = true;

        // ✅ VÉRIFIER SI UN RETARD EXISTE DÉJÀ POUR CETTE DATE
        $retardExistant = Retard::where('employe_id', $employe->id)
            ->whereDate('date', $dateStr)
            ->exists();

        if ($retardExistant) {
            $ignores++;
            continue;
        }

        // ✅ SI PAS D'HEURE D'ARRIVÉE ET PAS D'HEURE DE DÉPART → ABSENCE INJUSTIFIÉE
        if (empty($ha) && empty($hd)) {
            // ✅ VÉRIFIER SI UNE ABSENCE EXISTE DÉJÀ POUR CETTE DATE
            $absenceExiste = Absence::where('employe_id', $employe->id)
                ->whereDate('date_debut', '<=', $dateStr)
                ->whereDate('date_fin', '>=', $dateStr)
                ->exists();

            if (!$absenceExiste) {
                $reference = 'ABS-INJ-' . strtoupper(substr(uniqid(), -6));
                Absence::create([
                    'employe_id' => $employe->id,
                    'reference' => $reference,
                    'date_debut' => $dateStr,
                    'date_fin' => $dateStr,
                    'nombre_jours' => 1,
                    'motif' => 'Import Excel - Pas de pointage',
                    'type_journee' => 'journée complète',
                    'type_absence' => 'Absence injustifiée',
                    'justificatif_fourni' => false,
                    'statut' => 'refusé',
                    'observations' => 'Généré automatiquement depuis l\'import Excel (pas d\'heure d\'arrivée ni de départ)',
                ]);
                $absencesCrees++;
            }
            continue;
        }

        // Normaliser les heures
        $ha = $this->normaliserHeure($ha);
        $hd = $this->normaliserHeure($hd);

        // Calculer retard et heures sup
        [$minutesRetard, $minutesSup] = $this->calculerTemps($ha ?: '08:00', $hd ?: null);

        // Si pas de retard ET pas d'heures sup, créer une absence injustifiée
        if ($minutesRetard === 0 && $minutesSup === 0) {
            $absenceExiste = Absence::where('employe_id', $employe->id)
                ->whereDate('date_debut', '<=', $dateStr)
                ->whereDate('date_fin', '>=', $dateStr)
                ->exists();

            if (!$absenceExiste) {
                $reference = 'ABS-INJ-' . strtoupper(substr(uniqid(), -6));
                Absence::create([
                    'employe_id' => $employe->id,
                    'reference' => $reference,
                    'date_debut' => $dateStr,
                    'date_fin' => $dateStr,
                    'nombre_jours' => 1,
                    'motif' => 'Import Excel - Pointage sans retard',
                    'type_journee' => 'journée complète',
                    'type_absence' => 'Absence injustifiée',
                    'justificatif_fourni' => false,
                    'statut' => 'refusé',
                    'observations' => 'Généré automatiquement depuis l\'import Excel',
                ]);
                $absencesCrees++;
            }
            continue;
        }

        // Créer le retard
        Retard::create([
            'employe_id' => $employe->id,
            'date' => $dateStr,
            'heure_arrivee' => $ha ?: null,
            'heure_depart' => $hd ?: null,
            'minutes_retard' => $minutesRetard,
            'minutes_sup' => $minutesSup,
            'motif' => 'Import Excel',
        ]);

        $inseres++;
    }

    $msg = "Import : {$inseres} retard(s) importé(s).";
    if ($absencesCrees) $msg .= " {$absencesCrees} absence(s) injustifiée(s) créée(s).";
    if ($ignores) $msg .= " {$ignores} ignoré(s).";
    if (!empty($erreurs)) {
        session(['import_errors' => $erreurs]);
        $msg .= " " . count($erreurs) . " erreur(s).";
    }

    return back()->with('success', $msg);
}

    // Normalise HHhMM / HH:MM / HHMM → HH:MM
    private function normaliserHeure(?string $h): string
    {
        if (empty($h)) return '';
        $h = str_replace('h', ':', strtolower(trim($h)));
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $h, $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
        }
        if (preg_match('/^(\d{3,4})$/', $h, $m)) {
            $str = str_pad($m[1], 4, '0', STR_PAD_LEFT);
            return substr($str, 0, 2) . ':' . substr($str, 2, 2);
        }
        return $h;
    }

    public function pdfListe(Request $request)
    {
        $mois = $request->input('mois', now()->format('Y-m'));
        $q    = Retard::with('employe.direction');
        if (config('database.default') === 'sqlite') {
            $q->whereRaw("strftime('%Y-%m', date) = ?", [$mois]);
        } else {
            $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$mois]);
        }
        $retards      = $q->orderByDesc('date')->get();
        $parDirection = $retards->groupBy('employe.direction.nom')->map(fn($g) => ['nb' => $g->count()]);
        $pdf = Pdf::loadView('rh.retards.pdf_liste', compact('retards', 'parDirection', 'mois'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('retards_' . $mois . '.pdf');
    }

  /**
 * Nettoyer les doublons de retards
 */
public function nettoyerDoublons()
{
    // Récupérer les doublons (même employé, même date)
    $doublons = Retard::select('employe_id', 'date')
        ->groupBy('employe_id', 'date')
        ->havingRaw('COUNT(*) > 1')
        ->get();

    $supprimes = 0;
    $absencesCrees = 0;

    foreach ($doublons as $d) {
        $retards = Retard::where('employe_id', $d->employe_id)
            ->where('date', $d->date)
            ->orderBy('created_at')
            ->get();

        // Garder le premier, supprimer les autres
        $premier = $retards->shift();
        foreach ($retards as $r) {
            $r->delete();
            $supprimes++;
        }

        // ✅ VÉRIFIER SI UNE ABSENCE EXISTE DÉJÀ
        $absenceExiste = Absence::where('employe_id', $d->employe_id)
            ->whereDate('date_debut', '<=', $d->date)
            ->whereDate('date_fin', '>=', $d->date)
            ->exists();

        // Créer une absence uniquement si :
        // 1. Pas de retard (0 min) ET pas d'heures sup (0 min)
        // 2. Pas d'absence existante
        if ($premier->minutes_retard === 0 && $premier->minutes_sup === 0 && !$absenceExiste) {
            $reference = 'ABS-INJ-' . strtoupper(substr(uniqid(), -6));
            Absence::create([
                'employe_id' => $d->employe_id,
                'reference' => $reference,
                'date_debut' => $d->date,
                'date_fin' => $d->date,
                'nombre_jours' => 1,
                'motif' => 'Nettoyage doublons - Pointage sans retard',
                'type_journee' => 'journée complète',
                'type_absence' => 'Absence injustifiée',
                'justificatif_fourni' => false,
                'statut' => 'refusé',
                'observations' => 'Généré automatiquement après nettoyage des doublons',
            ]);
            $absencesCrees++;
        }
    }

    $message = "🧹 Nettoyage terminé : {$supprimes} doublon(s) supprimé(s)";
    if ($absencesCrees > 0) {
        $message .= ", {$absencesCrees} absence(s) injustifiée(s) créée(s)";
    }

    return back()->with('success', $message);
}

}