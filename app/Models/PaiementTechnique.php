<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementTechnique extends Model
{
    protected $table    = 'paiements_techniques';
    protected $fillable = ['dossier_client_id', 'montant', 'date_paiement', 'note'];

    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }
}