<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class PaiementDossier extends Model
{
    use Auditable;
    protected $table    = 'paiements_dossier';
    protected $fillable = [
        'dossier_client_id', 'client_id',
        'montant', 'reste', 'date_paiement', 'note','user_reference'
    ];

    public function dossierClient() { return $this->belongsTo(DossierClient::class); }
    public function client()        { return $this->belongsTo(Client::class); }
    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }
}