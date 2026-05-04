<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    protected $fillable = [
        'tf_id', 'client_id', 'commercial_id', 'conducteur_id', 'facilitateur_id',
        'svg_id', 'code', 'origine', 'type', 'color', 'owner_name',
        'superficie', 'prix',
        'date_prevue', 'date_confirmee', 'date_morcellement','reserved_client_id',
    ];

    protected $casts = [
        'date_prevue'       => 'date',
        'date_confirmee'    => 'date',
        'date_morcellement' => 'date',
    ];

    public function tf()          { return $this->belongsTo(Tf::class); }
    public function client()      { return $this->belongsTo(Client::class); }
    public function commercial()  { return $this->belongsTo(Commercial::class); }
    public function conducteur()  { return $this->belongsTo(Conducteur::class); }
    public function facilitateur(){ return $this->belongsTo(Facilitateur::class); }
    public function paiements()   { return $this->hasMany(Paiement::class); }
    public function dossier()     { return $this->hasOne(DossierTechnique::class); }
    public function reservedClient() { return $this->belongsTo(Client::class, 'reserved_client_id'); }
    public function dossierClient() { return $this->belongsTo(DossierClient::class); }
}