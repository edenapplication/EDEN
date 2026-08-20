<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierClient extends Model
{
    protected $table    = 'dossiers_clients';
    protected $fillable = [
        'client_id', 'nom_dossier',
        'commercial_id', 'conducteur_id',
        'facilitateur_id', 'agent_commercial_id',
        'grand_site_id', 'direction',
        'superficie_voulue', 'prix_superficie',
        'prix_technique', 'prix_morcellement','prix_logistique',
    ];

    public function client()          { return $this->belongsTo(Client::class); }
    public function commercial()      { return $this->belongsTo(Commercial::class); }
    public function conducteur()      { return $this->belongsTo(Conducteur::class); }
    public function facilitateur()    { return $this->belongsTo(Facilitateur::class); }
    public function agentCommercial() { return $this->belongsTo(AgentCommercial::class, 'agent_commercial_id'); }
    public function grandSite()       { return $this->belongsTo(GrandSite::class); }
    public function paiements()       { return $this->hasMany(PaiementDossier::class); }

    public function totalPaye(): float
    {
        return $this->paiements->sum('montant');
    }

    public function resteAPayer(): float
    {
        return max(0, ($this->prix_superficie ?? 0) - $this->totalPaye());
    }

    public function lots() { return $this->hasMany(Lot::class); }

    public function paiementsTechniques()
{
    return $this->hasMany(PaiementTechnique::class, 'dossier_client_id');
}

public function paiementsMorcellements()
{
    return $this->hasMany(PaiementMorcellement::class, 'dossier_client_id');
}

public function bons()
{
    return $this->hasMany(BonPaiement::class, 'dossier_client_id')
                ->orderByDesc('date_bon');
}

// Prix unitaire = prix superficie / superficie
public function getPrixUnitaireAttribute(): float
{
    if (!$this->superficie_voulue || $this->superficie_voulue == 0) return 0;
    return round(($this->prix_superficie ?? 0) / $this->superficie_voulue, 0);
}

public function paiementsLogistiques()
{
    return $this->hasMany(PaiementLogistique::class, 'dossier_client_id');
}
}