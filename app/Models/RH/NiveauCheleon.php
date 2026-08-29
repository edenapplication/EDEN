<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class NiveauCheleon extends Model
{
    protected $table = 'rh_niveaux_chelons';

    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'ordre',
        'description',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // ===== RELATIONS =====
    public function employes()
    {
        return $this->hasMany(Employe::class, 'niveau_chelon_id');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeOrdre($query)
    {
        return $query->orderBy('ordre');
    }

    public function scopeCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    // ===== ACCESSORS =====
    public function getLabelAttribute(): string
    {
        return $this->code . ' - ' . $this->nom;
    }
}