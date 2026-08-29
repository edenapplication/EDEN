<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class TrousseSecours extends Model
{
    protected $table = 'rh_trousses_secours';

    protected $fillable = [
        'localisation',
        'responsable',
        'date_verification',
        'prochaine_verification',
        'contenu',
        'inventaire_path',
        'statut',
    ];

    protected $casts = [
        'date_verification' => 'date',
        'prochaine_verification' => 'date',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'ok' => 'OK',
        'alerte' => 'Alerte (réapprovisionnement)',
        'vide' => 'Vide',
    ];

    const STATUTS_COLORS = [
        'ok' => '#dcfce7',
        'alerte' => '#fef3c7',
        'vide' => '#fee2e2',
    ];

    // ===== SCOPES =====
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeAVerifier($query, $jours = 30)
    {
        return $query->whereNotNull('prochaine_verification')
            ->whereBetween('prochaine_verification', [now(), now()->addDays($jours)]);
    }

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS_COLORS[$this->statut] ?? '#f1f5f9';
    }
}