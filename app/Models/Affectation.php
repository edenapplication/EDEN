<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    protected $table    = 'affectations';
    protected $fillable = [
        'grand_site_id','site_id','tf_id','bloc_id',
        'lot_affectation_id','client_id','dossier_client_id',
        'date_affectation','statut','notes',
    ];
    protected $casts = ['date_affectation' => 'date'];

    public function grandSite() { return $this->belongsTo(GrandSite::class, 'grand_site_id'); }
    public function site()      { return $this->belongsTo(Site::class, 'site_id'); }
    public function tf()        { return $this->belongsTo(Tf::class, 'tf_id'); }
    public function bloc()      { return $this->belongsTo(Bloc::class, 'bloc_id'); }
    public function lot()       { return $this->belongsTo(LotAffectation::class, 'lot_affectation_id'); }
    public function client()    { return $this->belongsTo(Client::class, 'client_id'); }
    public function dossier()   { return $this->belongsTo(DossierClient::class, 'dossier_client_id'); }

    // app/Models/Affectation.php

public function beneficiaire()
{
    return $this->belongsTo(Beneficiaire::class, 'beneficiaire_id');
}
}