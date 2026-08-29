<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HoraireService extends Model
{
    protected $table = 'rh_horaires_services';

    protected $fillable = [
        'service_id',
        'jour',
        'heure_debut',
        'heure_fin',
        'est_ferie',
        'est_travaille',
        'remarque'
    ];

    protected $casts = [
        'est_ferie' => 'boolean',
        'est_travaille' => 'boolean',
    ];

    // ===== CONSTANTES =====
    const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    // ===== RELATIONS =====
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // ===== SCOPES =====
    public function scopeTravaille($query)
    {
        return $query->where('est_travaille', true);
    }

    public function scopeFerie($query)
    {
        return $query->where('est_ferie', true);
    }

    public function scopeJour($query, $jour)
    {
        return $query->where('jour', $jour);
    }

    // ===== MÉTHODES MÉTIER =====

    /**
     * Vérifier si une heure d'arrivée est en retard
     */
    public function estEnRetard($heure): bool
    {
        if (!$this->heure_debut || !$this->est_travaille) {
            return false;
        }
        return strtotime($heure) > strtotime($this->heure_debut);
    }

    /**
     * Calculer les minutes de retard
     */
    public function minutesRetard($heureArrivee): int
    {
        if (!$this->estEnRetard($heureArrivee)) {
            return 0;
        }
        $debut = Carbon::parse($this->heure_debut);
        $arrivee = Carbon::parse($heureArrivee);
        return $debut->diffInMinutes($arrivee);
    }

    /**
     * Calculer les minutes supplémentaires
     */
    public function minutesSupplementaires($heureDepart): int
    {
        if (!$this->heure_fin || !$this->est_travaille || empty($heureDepart)) {
            return 0;
        }
        $fin = Carbon::parse($this->heure_fin);
        $depart = Carbon::parse($heureDepart);
        if ($depart->gt($fin)) {
            return $fin->diffInMinutes($depart);
        }
        return 0;
    }

    /**
     * Obtenir le temps travaillé en minutes
     */
    public function tempsTravailleMinutes($heureArrivee, $heureDepart): int
    {
        if (!$this->est_travaille || empty($heureArrivee) || empty($heureDepart)) {
            return 0;
        }
        $arrivee = Carbon::parse($heureArrivee);
        $depart = Carbon::parse($heureDepart);
        return $arrivee->diffInMinutes($depart);
    }

    // ===== ACCESSORS =====
    public function getPlageHoraireAttribute(): string
    {
        if (!$this->heure_debut && !$this->heure_fin) {
            return 'Non défini';
        }
        if (!$this->est_travaille) {
            return 'Non travaillé';
        }
        if ($this->est_ferie) {
            return 'Férié';
        }
        return $this->heure_debut . ' - ' . $this->heure_fin;
    }

    public function getJourLabelAttribute(): string
    {
        $labels = [
            'lundi' => 'Lundi',
            'mardi' => 'Mardi',
            'mercredi' => 'Mercredi',
            'jeudi' => 'Jeudi',
            'vendredi' => 'Vendredi',
            'samedi' => 'Samedi',
            'dimanche' => 'Dimanche'
        ];
        return $labels[$this->jour] ?? $this->jour;
    }
}