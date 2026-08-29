<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depart extends Model
{
    use SoftDeletes;

    protected $table = 'rh_departs';

    protected $fillable = [
        'employe_id',
        'motif_depart_id',
        'dernier_contrat_id',
        'date_depart',
        'date_notification',
        'date_preavis',
        'motif_libre',
        'lettre_demission_path',
        'attestation_travail_path',
        'certificat_travail_path',
        'statut',
        'valide_par',
        'date_validation',
        'observations',
    ];

    protected $casts = [
        'date_depart' => 'date',
        'date_notification' => 'date',
        'date_preavis' => 'date',
        'date_validation' => 'date',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'en_attente' => 'En attente',
        'valide' => 'Validé',
        'en_cours' => 'En cours',
        'termine' => 'Terminé',
        'annule' => 'Annulé'
    ];

    const STATUTS_COLORS = [
        'en_attente' => '#fef3c7',
        'valide' => '#dbeafe',
        'en_cours' => '#e0e7ff',
        'termine' => '#dcfce7',
        'annule' => '#fee2e2'
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function motifDepart()
    {
        return $this->belongsTo(MotifDepart::class, 'motif_depart_id');
    }

    public function dernierContrat()
    {
        return $this->belongsTo(Contrat::class, 'dernier_contrat_id');
    }

    public function soldeToutCompte()
    {
        return $this->hasOne(SoldeToutCompte::class);
    }

    public function certificatCessation()
    {
        return $this->hasOne(CertificatCessation::class);
    }

    public function validePar()
    {
        return $this->belongsTo(\App\Models\User::class, 'valide_par');
    }

    // ===== SCOPES =====
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS_COLORS[$this->statut] ?? '#f1f5f9';
    }

    // ===== MÉTHODES =====
    public function calculerSoldeToutCompte(): array
    {
        $employe = $this->employe;
        
        // Salaire de base (dernier contrat ou employé)
        $salaireBase = $employe->salaire_base;
        
        // Indemnité de congés (proportionnelle)
        $congesRestants = $employe->solde_conges_restant;
        $indemniteConges = round(($salaireBase / 30) * $congesRestants, 2);
        
        // Indemnité de préavis (selon le motif)
        $indemnitePreavis = 0;
        if ($this->motifDepart && $this->motifDepart->necessite_preavis) {
            $joursPreavis = $this->motifDepart->preavis_jours ?? 30;
            $indemnitePreavis = round(($salaireBase / 30) * $joursPreavis, 2);
        }
        
        // Prime d'ancienneté
        $ancienneteMois = $employe->date_integration->diffInMonths(now());
        $primeAnciennete = 0;
        if ($ancienneteMois >= 12) {
            $primeAnciennete = round($salaireBase * 0.05 * min(floor($ancienneteMois / 12), 10), 2);
        }
        
        // Total brut
        $totalBrut = $salaireBase + $indemniteConges + $indemnitePreavis + $primeAnciennete;
        
        // Déductions (CNPS, Impôts)
        $cnps = round($totalBrut * 0.0252, 2);
        $impots = round($totalBrut * 0.02, 2);
        $totalDeductions = $cnps + $impots;
        
        // Net à payer
        $netAPayer = max(0, $totalBrut - $totalDeductions);
        
        return [
            'salaire_base' => $salaireBase,
            'indemnite_conges' => $indemniteConges,
            'indemnite_preavis' => $indemnitePreavis,
            'prime_anciennete' => $primeAnciennete,
            'total_brut' => $totalBrut,
            'cnps' => $cnps,
            'impots' => $impots,
            'total_deductions' => $totalDeductions,
            'net_a_payer' => $netAPayer,
        ];
    }

    public function genererSoldeToutCompte(): SoldeToutCompte
    {
        $calcul = $this->calculerSoldeToutCompte();
        
        return SoldeToutCompte::create([
            'depart_id' => $this->id,
            'employe_id' => $this->employe_id,
            'salaire_base' => $calcul['salaire_base'],
            'indemnite_conges' => $calcul['indemnite_conges'],
            'indemnite_preavis' => $calcul['indemnite_preavis'],
            'prime_anciennete' => $calcul['prime_anciennete'],
            'total_brut' => $calcul['total_brut'],
            'cnps' => $calcul['cnps'],
            'impots' => $calcul['impots'],
            'total_deductions' => $calcul['total_deductions'],
            'net_a_payer' => $calcul['net_a_payer'],
            'statut' => 'a_payer',
        ]);
    }

    public function genererCertificatCessation(): CertificatCessation
    {
        return CertificatCessation::create([
            'depart_id' => $this->id,
            'employe_id' => $this->employe_id,
            'reference' => 'CERT-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
            'date_emission' => now(),
            'date_effet' => $this->date_depart,
            'motif' => $this->motifDepart?->nom ?? $this->motif_libre,
            'statut' => 'brouillon',
        ]);
    }
}