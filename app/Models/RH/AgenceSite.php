<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class AgenceSite extends Model
{
    protected $table = 'rh_agences_sites';

    protected $fillable = [
        'code',
        'nom',
        'ville',
        'adresse',
        'telephone',
        'description',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // ===== RELATIONS =====
    public function employes()
    {
        return $this->hasMany(Employe::class, 'agence_site_id');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeVille($query, $ville)
    {
        return $query->where('ville', $ville);
    }

    // ===== ACCESSORS =====
    public function getLocalisationAttribute(): string
    {
        return $this->ville ? $this->nom . ' - ' . $this->ville : $this->nom;
    }
}