<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'phone', 'email'];

    public function lots()          { return $this->hasMany(Lot::class); }
    public function dossiers()      { return $this->hasMany(DossierClient::class); }
    // Compatibilité ancienne relation
    public function dossierClient() { return $this->hasOne(DossierClient::class); }
    public function paiements()     { return $this->hasManyThrough(PaiementDossier::class, DossierClient::class); }
}