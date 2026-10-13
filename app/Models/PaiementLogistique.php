<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class PaiementLogistique extends Model
{
    use Auditable;
    protected $table    = 'paiements_logistiques';
    protected $fillable = ['dossier_client_id','montant','date_paiement','note'];

    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }
}