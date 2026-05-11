<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class BulletinPaie extends Model
{
    protected $table = 'rh_bulletins_paie';
    protected $fillable = [
        'employe_id','periode','mois_annee','date_paiement','vague',
        'salaire_brut','salaire_heure','prime','indemnite',
        'nb_heures_sup','montant_heures_sup','montant_fixe',
        'nb_retards','montant_retard','nb_absences','montant_absence',
        'acompte','pret','duree_pret','motif_sanction','duree_sanction',
        'montant_sanction','imputation_salaire','frais_bancaires','cnps',
        'net_a_payer','statut','observation',
    ];

    protected $casts = ['date_paiement' => 'date'];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }

    // Calcul automatique du net à payer
    public function calculerNet(): float
    {
        $brut = $this->salaire_brut + $this->montant_heures_sup + $this->prime + $this->indemnite + $this->montant_fixe;
        $deductions = $this->montant_retard + $this->montant_absence + $this->acompte
                    + $this->pret + $this->montant_sanction + $this->imputation_salaire
                    + $this->frais_bancaires + $this->cnps;
        return max(0, $brut - $deductions);
    }

    // Calcul salaire à l'heure (base 173.33 heures/mois)
    public function calculerSalaireHeure(): float
    {
        return $this->salaire_brut > 0 ? round($this->salaire_brut / 173.33, 4) : 0;
    }
}