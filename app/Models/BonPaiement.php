<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class BonPaiement extends Model
{
    use Auditable;

    protected $table    = 'bons_paiement';
    protected $fillable = [
        'dossier_client_id','numero_bon','date_bon',
        'versement_dossier','versement_technique',
        'versement_logistique','versement_morcellement',
        'total_dossier_cumul','total_technique_cumul',
        'total_logistique_cumul','total_morcellement_cumul',
        'afficher_reste','notes','user_reference',
        'user_id',
    ];
    protected $casts = [
        'date_bon'       => 'date',
        'afficher_reste' => 'boolean',
    ];

    // ═══════════════════════════════════════════════════════════════
    // ✅ SUPPRESSION AUTOMATIQUE DES PAIEMENTS ET VISITE LIÉS
    // (sécurité en plus du contrôleur)
    // ═══════════════════════════════════════════════════════════════
    protected static function booted(): void
    {
        static::deleting(function (BonPaiement $bon) {
            $dossierId = $bon->dossier_client_id;
            $reference = $bon->user_reference;
            $date      = $bon->date_bon;

            // Paiement Dossier
            if ($bon->versement_dossier > 0) {
                PaiementDossier::where('dossier_client_id', $dossierId)
                    ->whereDate('date_paiement', $date)
                    ->where('montant', $bon->versement_dossier)
                    ->when($reference, fn($q) => $q->where('user_reference', $reference))
                    ->delete();
            }

            // Paiement Technique
            if ($bon->versement_technique > 0) {
                PaiementTechnique::where('dossier_client_id', $dossierId)
                    ->whereDate('date_paiement', $date)
                    ->where('montant', $bon->versement_technique)
                    ->when($reference, fn($q) => $q->where('user_reference', $reference))
                    ->delete();
            }

            // Paiement Logistique
            if ($bon->versement_logistique > 0) {
                PaiementLogistique::where('dossier_client_id', $dossierId)
                    ->whereDate('date_paiement', $date)
                    ->where('montant', $bon->versement_logistique)
                    ->when($reference, fn($q) => $q->where('user_reference', $reference))
                    ->delete();
            }

            // Paiement Morcellement
            if ($bon->versement_morcellement > 0) {
                PaiementMorcellement::where('dossier_client_id', $dossierId)
                    ->whereDate('date_paiement', $date)
                    ->where('montant', $bon->versement_morcellement)
                    ->when($reference, fn($q) => $q->where('user_reference', $reference))
                    ->delete();
            }

            // Visite liée
            Visite::where('bon_id', $bon->id)->delete();
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════════════
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }

    public function visite()
    {
        return $this->hasOne(Visite::class, 'bon_id');
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