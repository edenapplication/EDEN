<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class TypeFormation extends Model
{
    protected $table = 'rh_types_formation';

    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'description',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // ===== CONSTANTES =====
    const CATEGORIES = ['INTERNE', 'EXTERNE', 'E_LEARNING', 'HYBRIDE'];

    // ===== RELATIONS =====
    public function formations()
    {
        return $this->hasMany(Formation::class, 'type_formation_id');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    // ===== ACCESSORS =====
    public function getCategorieLabelAttribute(): string
    {
        $labels = [
            'INTERNE' => 'Interne',
            'EXTERNE' => 'Externe',
            'E_LEARNING' => 'E-learning',
            'HYBRIDE' => 'Hybride'
        ];
        return $labels[$this->categorie] ?? $this->categorie;
    }
}