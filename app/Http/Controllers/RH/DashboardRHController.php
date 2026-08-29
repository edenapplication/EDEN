<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Employe;
use App\Models\RH\BulletinPaie;
use App\Models\RH\Absence;
use App\Models\RH\Pret;
use App\Models\RH\Sanction;
use App\Models\RH\Direction;
use App\Models\RH\CnpsAffiliation;
use App\Models\RH\CnpsDeclaration;
use App\Models\RH\Contrat;
use App\Models\RH\Conge;
use App\Models\RH\Retard;
use App\Models\RH\Alerte;
use Illuminate\Support\Carbon;

class DashboardRHController extends Controller
{
    public function index()
    {
        $periode = now()->format('Y-m');
        $moisActuel = Carbon::now();

        // ============================================================
        // 1. EFFECTIFS
        // ============================================================
        $totalEmployes = Employe::where('actif', true)->count();
        $totalHommes = Employe::where('actif', true)->where('sexe', 'M')->count();
        $totalFemmes = Employe::where('actif', true)->where('sexe', 'F')->count();
        $totalCDI = Employe::where('actif', true)->where('type_contrat', 'CDI')->count();
        $totalCDD = Employe::where('actif', true)->where('type_contrat', 'CDD')->count();
        $totalStages = Employe::where('actif', true)->where('type_contrat', 'STAGE')->count();

        $nouveauxCeMois = Employe::whereMonth('date_integration', $moisActuel->month)
            ->whereYear('date_integration', $moisActuel->year)->count();

        $departs = Employe::whereNotNull('date_sortie')
            ->whereMonth('date_sortie', $moisActuel->month)
            ->whereYear('date_sortie', $moisActuel->year)->count();

        $archives = Employe::where('actif', false)->count();

        // Évolution des effectifs (12 derniers mois)
        $evolutionEffectifs = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $moisActuel->copy()->subMonths($i);
            $evolutionEffectifs->push([
                'mois' => $date->translatedFormat('M Y'),
                'total' => Employe::whereMonth('date_integration', '<=', $date->month)
                    ->whereYear('date_integration', '<=', $date->year)
                    ->where(function($q) use ($date) {
                        $q->whereNull('date_sortie')
                            ->orWhere(function($q2) use ($date) {
                                $q2->whereMonth('date_sortie', '>=', $date->month)
                                    ->whereYear('date_sortie', '>=', $date->year);
                            });
                    })
                    ->count(),
            ]);
        }

        // ============================================================
        // 2. PAIE
        // ============================================================
        $masseSalariale = BulletinPaie::where('periode', $periode)->sum('net_a_payer');
        $masseBrute = BulletinPaie::where('periode', $periode)->sum('salaire_brut');
        $bulletinsValides = BulletinPaie::where('periode', $periode)->where('statut', 'validé')->count();
        $bulletinsTotal = BulletinPaie::where('periode', $periode)->count();
        $bulletinsPayes = BulletinPaie::where('periode', $periode)->where('statut', 'payé')->count();

        // Évolution paie (12 derniers mois)
        $evolutionPaie = collect();
        for ($i = 11; $i >= 0; $i--) {
            $p = $moisActuel->copy()->subMonths($i)->format('Y-m');
            $evolutionPaie->push([
                'mois' => $moisActuel->copy()->subMonths($i)->translatedFormat('M Y'),
                'brut' => BulletinPaie::where('periode', $p)->sum('salaire_brut'),
                'net' => BulletinPaie::where('periode', $p)->sum('net_a_payer'),
            ]);
        }

        // ============================================================
        // 3. ABSENCES & CONGÉS
        // ============================================================
        $absencesMois = Absence::whereMonth('date_debut', $moisActuel->month)
            ->whereYear('date_debut', $moisActuel->year)->count();

        $absencesEnAttente = Absence::where('statut', 'en_attente')->count();
        $absencesApprouvees = Absence::where('statut', 'approuvé')->count();
        $absencesRefusees = Absence::where('statut', 'refusé')->count();

        $congesEnCours = Conge::where('statut', 'en_cours')->count();
        $congesPlanifies = Conge::where('statut', 'planifie')->count();
        $congesTermines = Conge::where('statut', 'termine')->count();
        $congesAnnules = Conge::where('statut', 'annule')->count();

        // ============================================================
        // 4. RETARDS
        // ============================================================
        $retardsMois = Retard::whereMonth('date', $moisActuel->month)
            ->whereYear('date', $moisActuel->year)->count();

        $totalMinutesRetard = Retard::whereMonth('date', $moisActuel->month)
            ->whereYear('date', $moisActuel->year)->sum('minutes_retard');

        $totalMinutesSup = Retard::whereMonth('date', $moisActuel->month)
            ->whereYear('date', $moisActuel->year)->sum('minutes_sup');

        $employesAvecRetards = Retard::whereMonth('date', $moisActuel->month)
            ->whereYear('date', $moisActuel->year)
            ->distinct('employe_id')
            ->count('employe_id');

        // ============================================================
        // 5. PRÊTS
        // ============================================================
        $pretsEnCours = Pret::where('statut', 'en_cours')->sum('montant');
        $pretsNb = Pret::where('statut', 'en_cours')->count();
        $pretsRembourses = Pret::where('statut', 'rembourse')->count();
        $pretsAnnules = Pret::where('statut', 'annule')->count();

        // ============================================================
        // 6. SANCTIONS
        // ============================================================
        $sanctionsMois = Sanction::whereMonth('date', $moisActuel->month)
            ->whereYear('date', $moisActuel->year)->sum('montant');

        $sanctionsEnAttente = Sanction::where('statut', 'en_attente')->count();
        $sanctionsValides = Sanction::where('statut', 'valide')->count();

        // ============================================================
        // 7. CNPS
        // ============================================================
        $affilies = CnpsAffiliation::where('situation_affiliation', 'affilie')->count();
        $nonAffilies = CnpsAffiliation::where('situation_affiliation', 'non_affilie')->count();
        $sansAffiliation = Employe::where('actif', true)
            ->whereDoesntHave('cnpsAffiliation')
            ->count();

        $declarationMois = CnpsDeclaration::where('periode', $periode)->first();
        $cotisationsMois = $declarationMois ? $declarationMois->total_cnps : 0;
        $declarationsEnAttente = CnpsDeclaration::where('statut', 'a_declarer')->count();

        // ============================================================
        // 8. CONTRATS
        // ============================================================
        $contratsActifs = Contrat::where('statut', 'actif')->count();
        $contratsEnAttente = Contrat::where('statut', 'en_attente')->count();
        $contratsTermines = Contrat::whereIn('statut', ['termine', 'resilie'])->count();

        $alertePeriodeEssai = Contrat::alertePeriodeEssai(7)->count();
        $alerteFinCDD = Contrat::alerteFin(15)->count();

        // ============================================================
        // 9. ALERTES
        // ============================================================
        $alertesNonLues = Alerte::nonLu()->count();
        $alertesCritiques = Alerte::priorite('critique')->nonLu()->count();

        // ============================================================
        // 10. RÉPARTITION PAR DIRECTION
        // ============================================================
        $parDirection = Direction::withCount(['employes' => function($q) {
            $q->where('actif', true);
        }])->get();

        // Ajouter les totaux de paie par direction
        $parDirection->each(function($dir) use ($periode) {
            $dir->masse_salariale = BulletinPaie::where('periode', $periode)
                ->whereHas('employe', function($q) use ($dir) {
                    $q->where('direction_id', $dir->id);
                })
                ->sum('net_a_payer');
        });

        // ============================================================
        // 11. ANCIENNETÉ MOYENNE
        // ============================================================
        $employes = Employe::where('actif', true)->get();
        $ancMoyenne = $employes->avg(fn($e) => $e->date_integration->diffInMonths(now()));
        $ancMoyenneAns = round($ancMoyenne / 12, 1);

        // ============================================================
        // 12. DERNIÈRES ACTIVITÉS
        // ============================================================
        $derniersEmployes = Employe::with(['direction'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $derniersBulletins = BulletinPaie::with(['employe'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $dernieresAbsences = Absence::with(['employe'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $dernieresAlertes = Alerte::with(['employe'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ============================================================
        // 13. STATISTIQUES GLOBALES
        // ============================================================
        $statsGlobales = [
            'total_employes' => $totalEmployes,
            'hommes' => $totalHommes,
            'femmes' => $totalFemmes,
            'cdi' => $totalCDI,
            'cdd' => $totalCDD,
            'stages' => $totalStages,
            'taux_activite' => $totalEmployes > 0 ? round(($totalEmployes / ($totalEmployes + $archives)) * 100) : 0,
            'taux_femmes' => $totalEmployes > 0 ? round(($totalFemmes / $totalEmployes) * 100) : 0,
            'anciennete_moyenne' => $ancMoyenneAns,
        ];

        return view('rh.dashboard', compact(
            // Effectifs
            'totalEmployes', 'totalHommes', 'totalFemmes', 'totalCDI', 'totalCDD', 'totalStages',
            'nouveauxCeMois', 'departs', 'archives', 'evolutionEffectifs',
            
            // Paie
            'masseSalariale', 'masseBrute', 'bulletinsValides', 'bulletinsTotal', 'bulletinsPayes',
            'evolutionPaie',
            
            // Absences & Congés
            'absencesMois', 'absencesEnAttente', 'absencesApprouvees', 'absencesRefusees',
            'congesEnCours', 'congesPlanifies', 'congesTermines', 'congesAnnules',
            
            // Retards
            'retardsMois', 'totalMinutesRetard', 'totalMinutesSup', 'employesAvecRetards',
            
            // Prêts
            'pretsEnCours', 'pretsNb', 'pretsRembourses', 'pretsAnnules',
            
            // Sanctions
            'sanctionsMois', 'sanctionsEnAttente', 'sanctionsValides',
            
            // CNPS
            'affilies', 'nonAffilies', 'sansAffiliation',
            'cotisationsMois', 'declarationsEnAttente',
            
            // Contrats
            'contratsActifs', 'contratsEnAttente', 'contratsTermines',
            'alertePeriodeEssai', 'alerteFinCDD',
            
            // Alertes
            'alertesNonLues', 'alertesCritiques',
            
            // Répartition & Ancienneté
            'parDirection', 'ancMoyenneAns',
            
            // Dernières activités
            'derniersEmployes', 'derniersBulletins', 'dernieresAbsences', 'dernieresAlertes',
            
            // Stats globales
            'statsGlobales'
        ));
    }
}