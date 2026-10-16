<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiairePaiement extends Model
{
    protected $table = 'beneficiaire_paiements';

    protected $fillable = [
        'beneficiaire_id',
        'dossier_client_id',
        'paiement_type',
        'paiement_id',
        'montant',
        'date_paiement',
        'user_reference',
        'bon_id',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant'       => 'float',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════════

    public function beneficiaire()
    {
        return $this->belongsTo(Beneficiaire::class, 'beneficiaire_id');
    }

    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }

    public function bon()
    {
        return $this->belongsTo(BonPaiement::class, 'bon_id');
    }

    // ✅ Relation dynamique vers le paiement source
    public function paiementSource()
    {
        return match ($this->paiement_type) {
            'technique'   => PaiementTechnique::find($this->paiement_id),
            'logistique'  => PaiementLogistique::find($this->paiement_id),
            default       => null,
        };
    }
}