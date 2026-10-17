<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportLogistique extends Model
{
    protected $table = 'rapports_logistiques';

    protected $fillable = [
        'titre',
        'note',
        'type',
        'chemin_fichier',
        'nom_fichier',
        'nb_dossiers',
        'nb_beneficiaires',
        'total_prix',
        'total_paye',
        'total_reste',
        'total_global_encaisse',
        'filtres',
        'user_id',
    ];

    protected $casts = [
        'filtres' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->chemin_fichier) return null;
        return asset('storage/' . $this->chemin_fichier);
    }
}