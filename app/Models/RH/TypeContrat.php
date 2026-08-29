<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class TypeContrat extends Model
{
    protected $table = 'rh_types_contrat';

    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'est_renouvelable',
        'duree_maximale_mois',
        'periode_essai_max_jours',
        'description',
        'actif'
    ];

    protected $casts = [
        'est_renouvelable' => 'boolean',
        'actif' => 'boolean',
    ];

    // ===== RELATIONS =====
    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'type_contrat_id');
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

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    // ===== ACCESSORS =====
    public function getLabelAttribute(): string
    {
        return $this->nom . ($this->categorie ? ' (' . $this->categorie . ')' : '');
    }
}