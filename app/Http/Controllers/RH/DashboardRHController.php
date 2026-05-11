<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Employe;
use App\Models\RH\BulletinPaie;
use App\Models\RH\Absence;
use App\Models\RH\Pret;
use App\Models\RH\Sanction;
use App\Models\RH\Direction;
use Illuminate\Support\Carbon;

class DashboardRHController extends Controller
{
    public function index()
    {
        $periode = now()->format('Y-m');

        // KPIs principaux
        $totalEmployes      = Employe::where('actif', true)->count();
        $totalHommes        = Employe::where('actif', true)->where('sexe', 'M')->count();
        $totalFemmes        = Employe::where('actif', true)->where('sexe', 'F')->count();
        $totalCDI           = Employe::where('actif', true)->where('type_contrat', 'CDI')->count();
        $nouveauxCeMois     = Employe::whereMonth('date_integration', now()->month)
                                     ->whereYear('date_integration',  now()->year)->count();
        $departs            = Employe::whereNotNull('date_sortie')
                                     ->whereMonth('date_sortie', now()->month)
                                     ->whereYear('date_sortie',  now()->year)->count();

        // Paie du mois courant
        $masseSalariale     = BulletinPaie::where('periode', $periode)->sum('net_a_payer');
        $bulletinsValides   = BulletinPaie::where('periode', $periode)->where('statut', 'validé')->count();
        $bulletinsTotal     = BulletinPaie::where('periode', $periode)->count();

        // Absences du mois
        $absencesMois       = Absence::whereMonth('date_debut', now()->month)
                                     ->whereYear('date_debut', now()->year)->count();
        $absencesEnAttente  = Absence::where('statut', 'en_attente')->count();

        // Prêts en cours
        $pretsEnCours       = Pret::where('statut', 'en_cours')->sum('montant');
        $pretsNb            = Pret::where('statut', 'en_cours')->count();

        // Sanctions du mois
        $sanctionsMois      = Sanction::whereMonth('date', now()->month)
                                      ->whereYear('date', now()->year)->sum('montant');

        // Répartition par direction
        $parDirection       = Direction::withCount(['employes' => fn($q) => $q->where('actif', true)])
                                       ->get();

        // Évolution masse salariale (6 derniers mois)
        $evolutionPaie = collect();
        for ($i = 5; $i >= 0; $i--) {
            $p = now()->subMonths($i)->format('Y-m');
            $evolutionPaie->push([
                'mois'   => now()->subMonths($i)->translatedFormat('M Y'),
                'montant'=> BulletinPaie::where('periode', $p)->sum('net_a_payer'),
            ]);
        }

        // Ancienneté moyenne
        $employes       = Employe::where('actif', true)->get();
        $ancMoyenne     = $employes->avg(fn($e) => $e->date_integration->diffInMonths(now()));
        $ancMoyenneAns  = round($ancMoyenne / 12, 1);

        return view('rh.dashboard', compact(
            'totalEmployes', 'totalHommes', 'totalFemmes', 'totalCDI',
            'nouveauxCeMois', 'departs',
            'masseSalariale', 'bulletinsValides', 'bulletinsTotal',
            'absencesMois', 'absencesEnAttente',
            'pretsEnCours', 'pretsNb',
            'sanctionsMois', 'parDirection', 'evolutionPaie', 'ancMoyenneAns'
        ));
    }
}