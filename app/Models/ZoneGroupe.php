<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneGroupe extends Model
{
    protected $table = 'zone_groupes';

    protected $fillable = [
        'tf_id', 'client_id', 'dossier_client_id', 'nom', 'owner_name',
        'points', 'lot_ids', 'superficie_totale', 'type',
        'date_prevue', 'date_confirmee', 'date_morcellement',
    ];

    // ✅ Cast explicite — indispensable pour SQLite qui stocke JSON en TEXT
    protected $casts = [
        'points'  => 'array',
        'lot_ids' => 'array',
    ];

    public function tf()           { return $this->belongsTo(Tf::class); }
    public function client()       { return $this->belongsTo(Client::class); }
    public function dossierClient(){ return $this->belongsTo(DossierClient::class, 'dossier_client_id'); }
    public function dossierTechnique() { return $this->hasOne(DossierTechnique::class, 'zone_groupe_id'); }
}