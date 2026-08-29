<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class CnpsAffiliation extends Model
{
    protected $table = 'rh_cnps_affiliations';

    protected $fillable = [
        'employe_id',
        'numero_cnps',
        'date_affiliation',
        'centre_cnps',
        'situation_affiliation',
        'categorie_cnps',
        'salaire_soumis',
        'attestation_affiliation_path',
        'carte_cnps_path',
        'notes',
    ];

    protected $casts = [
        'date_affiliation' => 'date',
        'salaire_soumis' => 'decimal:2',
    ];

    // ===== CONSTANTES =====
    const SITUATIONS = [
        'affilie' => 'Affilié',
        'non_affilie' => 'Non affilié',
        'en_cours' => 'En cours d\'affiliation',
        'radie' => 'Radié'
    ];

    const SITUATIONS_COLORS = [
        'affilie' => '#dcfce7',
        'non_affilie' => '#fee2e2',
        'en_cours' => '#fef3c7',
        'radie' => '#f1f5f9'
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    // ===== SCOPES =====
    public function scopeAffilie($query)
    {
        return $query->where('situation_affiliation', 'affilie');
    }

    public function scopeNonAffilie($query)
    {
        return $query->where('situation_affiliation', 'non_affilie');
    }

    // ===== ACCESSORS =====
    public function getSituationLabelAttribute(): string
    {
        return self::SITUATIONS[$this->situation_affiliation] ?? $this->situation_affiliation;
    }

    public function getSituationColorAttribute(): string
    {
        return self::SITUATIONS_COLORS[$this->situation_affiliation] ?? '#f1f5f9';
    }
}