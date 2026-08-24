<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Visite extends Model
{
    protected $fillable = [
        'visiteur_id', 'client_id', 'dossier_client_id',
        'grand_site_id', 'site_id',
        'date_visite', 'heure_arrivee', 'heure_depart',
        'type_personne', 'note',
        'paiement_lie', 'paiement_id','bon_id',
    ];

    protected $casts = ['paiement_lie' => 'boolean', 'date_visite' => 'date',];

    public function visiteur()      { return $this->belongsTo(Visiteur::class); }
    public function client()        { return $this->belongsTo(Client::class); }
    public function dossierClient() { return $this->belongsTo(DossierClient::class); }
    public function grandSite()     { return $this->belongsTo(GrandSite::class); }
    public function site()          { return $this->belongsTo(Site::class); }
    public function paiement()      { return $this->belongsTo(PaiementDossier::class, 'paiement_id'); }
    public function bon()
{
    return $this->belongsTo(BonPaiement::class, 'bon_id');
}
    
}