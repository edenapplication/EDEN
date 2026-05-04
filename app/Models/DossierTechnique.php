<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierTechnique extends Model
{
    protected $table = 'dossiers_techniques';

    protected $fillable = [
        'lot_id',
        'statut',
        'progression',
        'date_sortie',
        'reference',
        // checklist
        'montage_dossier',
        'bon_pour_ccp',
        'controle',
        'mise_a_jour',
        'secretariat',
        'signature',
    ];

    protected $casts = [
        'montage_dossier' => 'boolean',
        'bon_pour_ccp'    => 'boolean',
        'controle'        => 'boolean',
        'mise_a_jour'     => 'boolean',
        'secretariat'     => 'boolean',
        'signature'       => 'boolean',
    ];

    // =============================================
    // Calcul automatique de la progression
    // =============================================
    public static array $etapes = [
        'montage_dossier' => 40,
        'bon_pour_ccp'    => 10,
        'controle'        => 15,
        'mise_a_jour'     => 10,
        'secretariat'     => 10,
        'signature'       => 15,
    ];

    public function calculerProgression(): int
    {
        $total = 0;
        foreach (self::$etapes as $champ => $poids) {
            if ($this->$champ) $total += $poids;
        }
        return $total;
    }

    public function calculerStatut(): string
    {
        $p = $this->calculerProgression();
        if ($p === 0)   return 'none';
        if ($p === 100) return 'complet';
        return 'en_cours';
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}