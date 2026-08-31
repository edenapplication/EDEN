<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Absence;
use App\Models\RH\Employe;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Absence::with('employe.direction');
        if ($request->filled('employe_id'))   $query->where('employe_id', $request->employe_id);
        if ($request->filled('type_absence')) $query->where('type_absence', $request->type_absence);
        if ($request->filled('statut'))       $query->where('statut', $request->statut);
        if ($request->filled('mois'))
            $query->whereRaw("DATE_FORMAT(date_debut, '%Y-%m') = ?", [$request->mois]);

        $absences = $query->orderByDesc('date_debut')->get();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        return view('rh.absences.index', compact('absences', 'employes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'   => 'required|exists:rh_employes,id',
            'type_absence' => 'required',
            'date_debut'   => 'required|date',
            'date_fin'     => 'required|date|after_or_equal:date_debut',
            'motif'        => 'nullable|string',
            'note'         => 'nullable|string',
        ]);
        $jours = (int) \Carbon\Carbon::parse($request->date_debut)
                    ->diffInWeekdays(\Carbon\Carbon::parse($request->date_fin)) + 1;
        $data = $request->all();
        $data['nombre_jours'] = $jours;
        $data['statut']       = 'en_attente';
        $data['reference']    = 'ABS-' . strtoupper(substr(uniqid(), -6));
        Absence::create($data);
        return back()->with('success', 'Absence enregistrée');
    }

    public function update(Request $request, $id)
    {
        $absence = Absence::findOrFail($id);
        $data    = $request->all();
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $data['nombre_jours'] = (int) \Carbon\Carbon::parse($request->date_debut)
                ->diffInWeekdays(\Carbon\Carbon::parse($request->date_fin)) + 1;
        }
        $absence->update($data);
        return back()->with('success', 'Absence mise à jour');
    }

    public function destroy($id)
    {
        Absence::findOrFail($id)->delete();
        return back()->with('success', 'Absence supprimée');
    }

    public function approuver($id)
    {
        Absence::findOrFail($id)->update(['statut' => 'approuvé']);
        return back()->with('success', 'Absence approuvée');
    }

    public function refuser($id)
    {
        Absence::findOrFail($id)->update(['statut' => 'refusé']);
        return back()->with('success', 'Absence refusée');
    }

    // ✅ Imprimer une demande d'absence
    public function pdf($id)
    {
        $absence = Absence::with('employe.direction')->findOrFail($id);
        $pdf = Pdf::loadView('rh.absences.pdf', compact('absence'))->setPaper('a4');
        return $pdf->download('absence_' . $absence->reference . '.pdf');
    }

    // ✅ Imprimer toute la liste
    public function pdfListe(Request $request)
    {
        $query = Absence::with('employe.direction');
        if ($request->filled('employe_id'))   $query->where('employe_id', $request->employe_id);
        if ($request->filled('statut'))       $query->where('statut', $request->statut);
        $absences = $query->orderByDesc('date_debut')->get();
        $pdf = Pdf::loadView('rh.absences.pdf_liste', compact('absences'))->setPaper('a4', 'landscape');
        return $pdf->download('absences_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Nettoyer les absences injustifiées en double
     */
    public function nettoyerAbsencesDoublons()
    {
        try {
            // Récupérer les doublons (même employé, même date)
            $doublons = DB::table('rh_absences')
                ->select('employe_id', 'date_debut', DB::raw('COUNT(*) as total'))
                ->where('type_absence', 'Absence injustifiée')
                ->where('statut', 'refusé')
                ->groupBy('employe_id', 'date_debut')
                ->having('total', '>', 1)
                ->get();

            if ($doublons->isEmpty()) {
                return back()->with('success', '✅ Aucune absence injustifiée en double trouvée.');
            }

            $totalSupprimees = 0;

            foreach ($doublons as $d) {
                $absences = Absence::where('employe_id', $d->employe_id)
                    ->where('date_debut', $d->date_debut)
                    ->where('type_absence', 'Absence injustifiée')
                    ->where('statut', 'refusé')
                    ->orderBy('created_at')
                    ->get();

                // Garder la première, supprimer les autres
                $absences->shift();
                foreach ($absences as $a) {
                    $a->delete();
                    $totalSupprimees++;
                }
            }

            return back()->with('success', "✅ Nettoyage terminé. {$totalSupprimees} absence(s) injustifiée(s) en double supprimée(s).");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du nettoyage : ' . $e->getMessage());
        }
    }
}