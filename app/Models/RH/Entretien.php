<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class Entretien extends Model
{
    protected $table = 'rh_entretiens';

    protected $fillable = [
        'candidat_id',
        'type',
        'date_entretien',
        'heure',
        'lieu',
        'evaluateur',
        'evaluateur_poste',
        'note',
        'points_forts',
        'points_faibles',
        'remarques',
        'decision',
    ];

    protected $casts = [
        'date_entretien' => 'date',
        'heure' => 'datetime:H:i',
        'note' => 'integer',
    ];

    // ===== CONSTANTES =====
    const TYPES = [
        'rh' => 'Entretien RH',
        'hierarchique' => 'Entretien hiérarchique',
        'technique' => 'Entretien technique',
        'final' => 'Entretien final'
    ];

    const DECISIONS = [
        'positif' => 'Positif',
        'negatif' => 'Négatif',
        'en_attente' => 'En attente'
    ];

    const DECISIONS_COLORS = [
        'positif' => '#dcfce7',
        'negatif' => '#fee2e2',
        'en_attente' => '#fef3c7'
    ];

    // ===== RELATIONS =====
    public function candidat()
    {
        return $this->belongsTo(Candidat::class, 'candidat_id');
    }

    // ===== SCOPES =====
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePositif($query)
    {
        return $query->where('decision', 'positif');
    }

    // ===== ACCESSORS =====
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDecisionLabelAttribute(): string
    {
        return self::DECISIONS[$this->decision] ?? $this->decision;
    }

    public function getDecisionColorAttribute(): string
    {
        return self::DECISIONS_COLORS[$this->decision] ?? '#f1f5f9';
    }

    public function getNoteEtoilesAttribute(): string
    {
        if (!$this->note) return '-';
        return str_repeat('⭐', $this->note);
    }
}