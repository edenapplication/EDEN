<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierTechnique extends Model
{
    protected $table = 'dossiers_techniques';

    protected $fillable = [
        'lot_id', 'zone_groupe_id',
        'statut', 'progression',
        'montage_dossier', 'bon_pour_ccp', 'controle',
        'mise_a_jour', 'secretariat', 'signature',
    ];

    protected $casts = [
        'montage_dossier' => 'boolean',
        'bon_pour_ccp'    => 'boolean',
        'controle'        => 'boolean',
        'mise_a_jour'     => 'boolean',
        'secretariat'     => 'boolean',
        'signature'       => 'boolean',
    ];

    public function lot()        { return $this->belongsTo(Lot::class); }
    public function zoneGroupe() { return $this->belongsTo(ZoneGroupe::class, 'zone_groupe_id'); }

    // Poids de chaque étape
    public static array $poids = [
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
        foreach (self::$poids as $champ => $poids) {
            if ($this->$champ) $total += $poids;
        }
        return $total;
    }

    public function calculerStatut(): string
    {
        $prog = $this->calculerProgression();
        if ($prog === 0)   return 'none';
        if ($prog === 100) return 'complet';
        return 'en_cours';
    }
}