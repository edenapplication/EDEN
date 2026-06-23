<?php
namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Conge extends Model
{
    protected $table    = 'rh_conges';
    protected $fillable = [
        'employe_id','date_debut','date_fin',
        'nb_jours','motif','statut','notes',
    ];
    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

  public static function calculerDateFin($dateDebut, $nbJours)
{
    $date = Carbon::parse($dateDebut);

    $compteur = 1;

    while ($compteur < $nbJours) {

        $date->addDay();

        if (!$date->isSunday()) {
            $compteur++;
        }
    }

    return $date;
}

    // Statut lisible
    public function getStatutLabelAttribute(): string
    {
        return [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine'  => 'Terminé',
            'annule'   => 'Annulé',
        ][$this->statut] ?? $this->statut;
    }

    // Couleur badge
    public function getStatutColorAttribute(): string
    {
        return [
            'planifie' => '#f59e0b',
            'en_cours' => '#1d4ed8',
            'termine'  => '#16a34a',
            'annule'   => '#dc2626',
        ][$this->statut] ?? '#64748b';
    }
}