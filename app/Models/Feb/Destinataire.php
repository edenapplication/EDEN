<?php
// app/Models/Feb/Destinataire.php

namespace App\Models\Feb;

use Illuminate\Database\Eloquent\Model;

class Destinataire extends Model
{
    protected $table = 'feb_destinataires';
    
    protected $fillable = [
        'nom'
    ];

    /**
     * Relation avec les fiches
     * Un destinataire peut être associé à plusieurs fiches
     */
    public function fiches()
    {
        return $this->belongsToMany(
            Fiche::class,
            'feb_destinataire_fiche',
            'destinataire_id',
            'fiche_id'
        )->withPivot('ordre')
         ->orderBy('pivot_ordre');
    }

    /**
     * Scope pour la recherche
     */
    public function scopeRecherche($query, $terme)
    {
        return $query->where('nom', 'LIKE', "%{$terme}%");
    }

    /**
     * Scope pour les destinataires actifs
     * (si vous ajoutez un champ actif plus tard)
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}