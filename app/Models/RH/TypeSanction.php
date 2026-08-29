<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class TypeSanction extends Model
{
    protected $table = 'rh_types_sanction';

    protected $fillable = [
        'code',
        'nom',
        'gravite',
        'impact_financier',
        'impact_carriere',
        'duree_max_jours',
        'description',
        'actif'
    ];

    protected $casts = [
        'impact_financier' => 'boolean',
        'impact_carriere' => 'boolean',
        'actif' => 'boolean',
    ];

    // ===== CONSTANTES =====
    const GRAVITES = ['faible', 'moyenne', 'elevee', 'tres_elevee'];

    // ===== RELATIONS =====
    public function sanctions()
    {
        return $this->hasMany(Sanction::class, 'type_sanction_id');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeGravite($query, $gravite)
    {
        return $query->where('gravite', $gravite);
    }

    public function scopeImpactFinancier($query)
    {
        return $query->where('impact_financier', true);
    }

    // ===== ACCESSORS =====
    public function getGraviteLabelAttribute(): string
    {
        $labels = [
            'faible' => 'Faible',
            'moyenne' => 'Moyenne',
            'elevee' => 'Élevée',
            'tres_elevee' => 'Très élevée'
        ];
        return $labels[$this->gravite] ?? $this->gravite;
    }

    public function getGraviteColorAttribute(): string
    {
        $colors = [
            'faible' => '#22c55e',     // vert
            'moyenne' => '#f59e0b',    // orange
            'elevee' => '#ef4444',     // rouge
            'tres_elevee' => '#7f1d1d' // rouge foncé
        ];
        return $colors[$this->gravite] ?? '#64748b';
    }
}