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
        'type_personne',
        'objet', 'motif',                       // ✅ nouveaux champs
        'note',
        'paiement_lie', 'paiement_id', 'bon_id',
    ];

    protected $casts = [
        'paiement_lie' => 'boolean',
        'date_visite'  => 'date',
    ];

    // ============================================
    // ✅ TYPES DE PERSONNE
    // ============================================
    public const TYPES_PERSONNE = [
        'client'       => 'Client',
        'proprietaire' => 'Propriétaire',
        'autre'        => 'Autre',
    ];

    // ============================================
    // ✅ OBJETS DE VISITE
    // Ajoutez / modifiez librement ici, sans toucher à la BDD
    // ============================================
    public const OBJETS = [
        'descente_terrain' => [
            'label'           => 'Descente terrain',
            'necessite_motif' => false,
            'couleur'         => 'bleu',
        ],
        'reception' => [
            'label'           => 'Réception',
            'necessite_motif' => true,
            'couleur'         => 'jaune',
        ],
        // 👇 Exemples — décommentez si besoin
        // 'livraison' => [
        //     'label'           => 'Livraison',
        //     'necessite_motif' => true,
        //     'couleur'         => 'vert',
        // ],
        // 'maintenance' => [
        //     'label'           => 'Maintenance',
        //     'necessite_motif' => false,
        //     'couleur'         => 'violet',
        // ],
    ];

    /**
     * Liste des codes d'objets (pour la validation).
     */
    public static function codesObjets(): array
    {
        return array_keys(self::OBJETS);
    }

    /**
     * Indique si un objet donné nécessite un motif.
     */
    public static function objetNecessiteMotif(?string $code): bool
    {
        return self::OBJETS[$code]['necessite_motif'] ?? false;
    }

    // ============================================
    // ✅ ACCESSORS
    // ============================================
    public function getTypePersonneLibelleAttribute(): string
    {
        return self::TYPES_PERSONNE[$this->type_personne] ?? ucfirst((string) $this->type_personne);
    }

    public function getObjetLibelleAttribute(): string
    {
        return self::OBJETS[$this->objet]['label'] ?? '—';
    }

    public function getObjetCouleurAttribute(): string
    {
        return self::OBJETS[$this->objet]['couleur'] ?? 'gris';
    }

    // ============================================
    // ✅ RELATIONS (inchangées)
    // ============================================
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