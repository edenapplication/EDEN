<?php
// app/Models/HistoriqueAffectation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueAffectation extends Model
{
    protected $table = 'historique_affectations';

    protected $fillable = [
        'dossier_client_id',
        'type_action',
        'user_id',
        'cible_type',
        'cible_id',
        'resume',
        'donnees_avant',
        'donnees_apres',
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
    ];

    // ════════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════════
    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ════════════════════════════════════════════════════════════
    // LABELS & ICONES
    // ════════════════════════════════════════════════════════════
    public function getIconeAttribute(): string
    {
        return match ($this->type_action) {
            'ajout_beneficiaire'         => '➕',
            'modification_beneficiaire'  => '✏️',
            'suppression_beneficiaire'   => '🗑️',
            'affectation_lot'            => '📦',
            'annulation_affectation'     => '↩️',
            default                      => '📌',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->type_action) {
            'ajout_beneficiaire'         => '#16a34a',
            'modification_beneficiaire'  => '#f59e0b',
            'suppression_beneficiaire'   => '#dc2626',
            'affectation_lot'            => '#0d6efd',
            'annulation_affectation'     => '#7c3aed',
            default                      => '#64748b',
        };
    }
}