<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\BulletinPaie;
use App\Models\RH\Employe;
use App\Models\RH\Pret;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaieController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->periode ?? now()->format('Y-m');
        $vague   = $request->vague   ?? null;
        $query   = BulletinPaie::with('employe.direction')->where('periode', $periode);
        if ($vague) $query->where('vague', $vague);
        $bulletins     = $query->orderBy('vague')->get();
        $totalNet      = $bulletins->sum('net_a_payer');
        $totalBrut     = $bulletins->sum('salaire_brut');
        $totalSanction = $bulletins->sum('montant_sanction');
        $totalAcompte  = $bulletins->sum('acompte');
        return view('rh.paie.index', compact('bulletins','periode','vague','totalNet','totalBrut','totalSanction','totalAcompte'));
    }

    public function create()
    {
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        return view('rh.paie.create', compact('employes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'    => 'required|exists:rh_employes,id',
            'periode'       => 'required',
            'vague'         => 'required|in:VAGUE 1,VAGUE 2',
            'date_paiement' => 'required|date',
            'salaire_brut'  => 'required|numeric|min:0',
        ]);
        $salaireHeure = $request->salaire_brut / 173.33;
        $montantHS    = round($request->nb_heures_sup * $salaireHeure, 2);
        $data = $request->all();
        $data['salaire_heure']      = round($salaireHeure, 4);
        $data['montant_heures_sup'] = $montantHS;
        $brut      = $request->salaire_brut + $montantHS + ($request->prime ?? 0) + ($request->indemnite ?? 0) + ($request->montant_fixe ?? 1000);
        $deductions = ($request->montant_retard ?? 0) + ($request->montant_absence ?? 0) + ($request->acompte ?? 0)
                    + ($request->pret ?? 0) + ($request->montant_sanction ?? 0) + ($request->imputation_salaire ?? 0)
                    + ($request->frais_bancaires ?? 0) + ($request->cnps ?? 0);
        $data['net_a_payer'] = max(0, $brut - $deductions);
        $data['mois_annee']  = \Carbon\Carbon::createFromFormat('Y-m', $request->periode)->translatedFormat('F Y');
        BulletinPaie::create($data);
        return redirect()->route('rh.paie.index', ['periode' => $request->periode])->with('success', 'Bulletin créé');
    }

    public function show($id)
    {
        $bulletin = BulletinPaie::with('employe.direction')->findOrFail($id);
        return view('rh.paie.show', compact('bulletin'));
    }

    public function update(Request $request, $id)
    {
        $bulletin = BulletinPaie::findOrFail($id);
        if ($bulletin->statut === 'payé') return back()->with('error', 'Impossible de modifier un bulletin payé.');
        $data = $request->all();
        $salaireHeure = ($request->salaire_brut ?? $bulletin->salaire_brut) / 173.33;
        $montantHS    = round(($request->nb_heures_sup ?? 0) * $salaireHeure, 2);
        $data['salaire_heure']      = round($salaireHeure, 4);
        $data['montant_heures_sup'] = $montantHS;
        $brut      = ($request->salaire_brut ?? 0) + $montantHS + ($request->prime ?? 0) + ($request->indemnite ?? 0) + ($request->montant_fixe ?? 1000);
        $deductions = ($request->montant_retard ?? 0) + ($request->montant_absence ?? 0) + ($request->acompte ?? 0)
                    + ($request->pret ?? 0) + ($request->montant_sanction ?? 0) + ($request->imputation_salaire ?? 0)
                    + ($request->frais_bancaires ?? 0) + ($request->cnps ?? 0);
        $data['net_a_payer'] = max(0, $brut - $deductions);
        $bulletin->update($data);
        return back()->with('success', 'Bulletin mis à jour');
    }

    public function destroy($id)
    {
        $bulletin = BulletinPaie::findOrFail($id);
        if ($bulletin->statut === 'payé') return back()->with('error', 'Impossible de supprimer un bulletin payé.');
        $bulletin->delete();
        return back()->with('success', 'Bulletin supprimé');
    }

    public function valider($id)
    {
        $bulletin = BulletinPaie::findOrFail($id);
        $bulletin->update(['statut' => $bulletin->statut === 'brouillon' ? 'validé' : 'payé']);
        return back()->with('success', 'Statut mis à jour');
    }

    public function pdf($id)
    {
        $bulletin = BulletinPaie::with('employe.direction')->findOrFail($id);
        $pdf = Pdf::loadView('rh.paie.pdf', compact('bulletin'))->setPaper('a4');
        return $pdf->download('bulletin_' . $bulletin->employe->matricule . '_' . $bulletin->periode . '.pdf');
    }

    // ✅ PDF liste des bulletins avec solde prêt restant
    public function pdfListe(Request $request)
{
    $periode = $request->periode ?? now()->format('Y-m');
    $vague   = $request->vague;

    $query = BulletinPaie::with('employe.direction')
                ->where('periode', $periode);

    if ($vague) {
        $query->where('vague', $vague);
    }

    $bulletins = $query->orderBy('vague')->get();

    $bulletins->each(function($b) {
        $pretRestant = \App\Models\RH\Pret::where('employe_id', $b->employe_id)
            ->where('statut', 'en_cours')
            ->get()
            ->sum(fn($p) => max(0, $p->montant - $p->montant_rembourse));

        $b->pret_restant = $pretRestant;
    });

    $pdf = Pdf::loadView(
        'rh.paie.pdf_liste',
        compact('bulletins', 'periode', 'vague')
    )->setPaper('a4', 'landscape');

    $nom = 'bulletins_'.$periode;

    if ($vague) {
        $nom .= '_'.str_replace(' ', '_', strtolower($vague));
    }

    return $pdf->download($nom.'.pdf');
}

   public function recapitulatif(Request $request)
{
    $periode = $request->periode ?? now()->format('Y-m');
    $vague   = $request->vague;

    $query = BulletinPaie::with('employe.direction')
                ->where('periode', $periode);

    if ($vague) {
        $query->where('vague', $vague);
    }

    $bulletins = $query->get();

    $parDirection = $bulletins->groupBy('employe.direction.nom')->map(fn($g) => [
        'nb'       => $g->count(),
        'brut'     => $g->sum('salaire_brut'),
        'net'      => $g->sum('net_a_payer'),
        'hs'       => $g->sum('montant_heures_sup'),
        'sanction' => $g->sum('montant_sanction'),
        'acomptes' => $g->sum('acompte'),
    ]);

    if ($request->has('pdf')) {
        $pdf = Pdf::loadView(
            'rh.paie.recapitulatif_pdf',
            compact('bulletins', 'periode', 'parDirection', 'vague')
        )->setPaper('a4', 'landscape');

        $nom = 'recapitulatif_'.$periode;

        if ($vague) {
            $nom .= '_'.str_replace(' ', '_', strtolower($vague));
        }

        return $pdf->download($nom.'.pdf');
    }

    return view('rh.paie.recapitulatif',
        compact('bulletins', 'periode', 'parDirection', 'vague'));
}

    public function genererMasse(Request $request)
    {
        $request->validate([
            'periode'       => 'required',
            'vague'         => 'required|in:VAGUE 1,VAGUE 2',
            'date_paiement' => 'required|date',
        ]);
        $employes = Employe::where('actif', true)->where('vague_paiement', $request->vague)->get();
        $crees = 0;
        foreach ($employes as $e) {
            $existe = BulletinPaie::where('employe_id', $e->id)
                ->where('periode', $request->periode)
                ->where('vague', $request->vague)->exists();
            if (!$existe) {
                $sh  = $e->salaire_base / 173.33;
                $net = $e->salaire_base + 1000;
                BulletinPaie::create([
                    'employe_id'    => $e->id,
                    'periode'       => $request->periode,
                    'mois_annee'    => \Carbon\Carbon::createFromFormat('Y-m', $request->periode)->translatedFormat('F Y'),
                    'date_paiement' => $request->date_paiement,
                    'vague'         => $request->vague,
                    'salaire_brut'  => $e->salaire_base,
                    'salaire_heure' => round($sh, 4),
                    'montant_fixe'  => 1000,
                    'net_a_payer'   => $net,
                    'statut'        => 'brouillon',
                ]);
                $crees++;
            }
        }
        return back()->with('success', "{$crees} bulletin(s) générés pour {$request->vague} — {$request->periode}");
    }
}