<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class SoldeToutCompte extends Model
{
    protected $table = 'rh_soldes_tout_compte';

    protected $fillable = [
        'depart_id',
        'employe_id',
        'salaire_base',
        'indemnite_conges',
        'indemnite_preavis',
        'indemnite_licenciement',
        'prime_anciennete',
        'autres_indemnites',
        'total_brut',
        'cnps',
        'impots',
        'autres_deductions',
        'total_deductions',
        'net_a_payer',
        'date_paiement',
        'mode_paiement',
        'reference_paiement',
        'statut',
        'document_path',
        'observations',
    ];

    protected $casts = [
        'salaire_base' => 'decimal:2',
        'total_brut' => 'decimal:2',
        'net_a_payer' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'a_payer' => 'À payer',
        'paye' => 'Payé',
        'annule' => 'Annulé'
    ];

    // ===== RELATIONS =====
    public function depart()
    {
        return $this->belongsTo(Depart::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    // ===== SCOPES =====
    public function scopeAPayer($query)
    {
        return $query->where('statut', 'a_payer');
    }

    public function scopePaye($query)
    {
        return $query->where('statut', 'paye');
    }

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getMontantEnLettresAttribute(): string
    {
        // Fonction de conversion en lettres (à implémenter)
        return $this->net_a_payer . ' FCFA';
    }
}