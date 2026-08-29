<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class MotifDepart extends Model
{
    protected $table = 'rh_motifs_depart';

    protected $fillable = [
        'code',
        'nom',
        'categorie',
        'est_volontaire',
        'necessite_preavis',
        'preavis_jours',
        'description',
        'actif'
    ];

    protected $casts = [
        'est_volontaire' => 'boolean',
        'necessite_preavis' => 'boolean',
        'actif' => 'boolean',
    ];

    // ===== CONSTANTES =====
    const CATEGORIES = ['DEMISSION', 'LICENCIEMENT', 'FIN_CONTRAT', 'RETRAITE', 'AUTRE'];

    // ===== RELATIONS =====
    public function departs()
    {
        return $this->hasMany(Depart::class, 'motif_depart_id');
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

    public function scopeVolontaire($query)
    {
        return $query->where('est_volontaire', true);
    }

    public function scopeNonVolontaire($query)
    {
        return $query->where('est_volontaire', false);
    }

    // ===== ACCESSORS =====
    public function getCategorieLabelAttribute(): string
    {
        $labels = [
            'DEMISSION' => 'Démission',
            'LICENCIEMENT' => 'Licenciement',
            'FIN_CONTRAT' => 'Fin de contrat',
            'RETRAITE' => 'Retraite',
            'AUTRE' => 'Autre'
        ];
        return $labels[$this->categorie] ?? $this->categorie;
    }

    public function getPreavisLabelAttribute(): string
    {
        if (!$this->necessite_preavis) {
            return 'Sans préavis';
        }
        return $this->preavis_jours ? $this->preavis_jours . ' jours' : 'Préavis requis';
    }
}