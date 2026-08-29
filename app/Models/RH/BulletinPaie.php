<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class BulletinPaie extends Model
{
    protected $table = 'rh_bulletins_paie';
    
    protected $fillable = [
        'employe_id',
        'contrat_id',  // ✅ NOUVEAU
        'periode',
        'mois_annee',
        'date_paiement',
        'vague',
        'salaire_brut',
        'base_cnps',  // ✅ NOUVEAU
        'salaire_heure',
        'prime',
        'indemnite',
        'nb_heures_sup',
        'montant_heures_sup',
        'montant_fixe',
        'nb_retards',
        'montant_retard',
        'nb_absences',
        'montant_absence',
        'acompte',
        'pret',
        'duree_pret',
        'motif_sanction',
        'duree_sanction',
        'montant_sanction',
        'imputation_salaire',
        'frais_bancaires',
        'cnps',
        'cnps_salariale',  // ✅ NOUVEAU
        'cnps_patronale',  // ✅ NOUVEAU
        'net_a_payer',
        'statut',
        'observation',
        'est_generer_auto',  // ✅ NOUVEAU
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'est_generer_auto' => 'boolean',
        'salaire_brut' => 'decimal:2',
        'base_cnps' => 'decimal:2',
        'cnps_salariale' => 'decimal:2',
        'cnps_patronale' => 'decimal:2',
        'cnps' => 'decimal:2',
        'net_a_payer' => 'decimal:2',
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class, 'contrat_id');
    }

    // ===== MÉTHODES MÉTIER =====

    /**
     * Calculer le salaire horaire (base 173.33h/mois)
     */
    public function calculerSalaireHeure(): float
    {
        return $this->salaire_brut > 0 ? round($this->salaire_brut / 173.33, 4) : 0;
    }

    /**
     * Calculer les cotisations CNPS
     * Taux : Salarié 2.52%, Patronal 4.20%, Total 6.72%
     */
    public function calculerCnps(): array
    {
        $base = $this->base_cnps ?? $this->salaire_brut;
        
        $salariale = round($base * 0.0252, 2);
        $patronale = round($base * 0.0420, 2);
        $total = $salariale + $patronale;

        return [
            'base' => $base,
            'salariale' => $salariale,
            'patronale' => $patronale,
            'total' => $total,
        ];
    }

    /**
     * Calculer le net à payer
     */
    public function calculerNet(): float
    {
        $brut = $this->salaire_brut + ($this->montant_heures_sup ?? 0) + ($this->prime ?? 0) 
                + ($this->indemnite ?? 0) + ($this->montant_fixe ?? 0);
        
        $deductions = ($this->montant_retard ?? 0) + ($this->montant_absence ?? 0) 
                    + ($this->acompte ?? 0) + ($this->pret ?? 0) 
                    + ($this->montant_sanction ?? 0) + ($this->imputation_salaire ?? 0)
                    + ($this->frais_bancaires ?? 0) + ($this->cnps ?? 0);
        
        return max(0, $brut - $deductions);
    }

    /**
     * Calculer et mettre à jour tous les totaux
     */
    public function recalculer(): void
    {
        // Calculer le salaire horaire
        $this->salaire_heure = $this->calculerSalaireHeure();

        // Calculer les heures sup
        if ($this->nb_heures_sup > 0) {
            $this->montant_heures_sup = round($this->nb_heures_sup * ($this->salaire_brut / 173.33), 2);
        }

        // Calculer les cotisations CNPS
        $cnps = $this->calculerCnps();
        $this->base_cnps = $cnps['base'];
        $this->cnps_salariale = $cnps['salariale'];
        $this->cnps_patronale = $cnps['patronale'];
        $this->cnps = $cnps['total'];

        // Calculer le net
        $this->net_a_payer = $this->calculerNet();
    }
}