<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonPaiement extends Model
{
    protected $table    = 'bons_paiement';
    protected $fillable = [
        'dossier_client_id','numero_bon','date_bon',
        'versement_dossier','versement_technique',
        'versement_logistique','versement_morcellement',
        'total_dossier_cumul','total_technique_cumul',
        'total_logistique_cumul','total_morcellement_cumul',
        'afficher_reste','notes',
    ];
    protected $casts = [
        'date_bon'       => 'date',
        'afficher_reste' => 'boolean',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }

    // Total versé ce bon
    public function getTotalVersementAttribute(): float
    {
        return $this->versement_dossier
             + $this->versement_technique
             + $this->versement_logistique
             + $this->versement_morcellement;
    }

    // Total cumulé tous types
    public function getTotalCumulAttribute(): float
    {
        return $this->total_dossier_cumul
             + $this->total_technique_cumul
             + $this->total_logistique_cumul
             + $this->total_morcellement_cumul;
    }

    // Générer un numéro unique
    public static function genererNumero(): string
    {
        $dernier = self::orderByDesc('id')->first();
        $num     = $dernier ? $dernier->id + 1 : 1;
        return 'EDG-BON-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}