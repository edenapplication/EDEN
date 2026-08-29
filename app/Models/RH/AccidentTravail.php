<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AccidentTravail extends Model
{
    protected $table = 'rh_accidents_travail';

    protected $fillable = [
        'employe_id',
        'declare_par',
        'date_accident',
        'heure',
        'lieu',
        'circonstances',
        'description',
        'nature_blessures',
        'temoin1_nom',
        'temoin1_tel',
        'temoin2_nom',
        'temoin2_tel',
        'prise_en_charge',
        'suivi',
        'date_retour',
        'rapport_path',
        'constat_path',
        'certificat_medical_path',
        'statut',
    ];

    protected $casts = [
        'date_accident' => 'date',
        'heure' => 'datetime:H:i',
        'date_retour' => 'date',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'declare' => 'Déclaré',
        'en_cours' => 'En cours de traitement',
        'cloture' => 'Clôturé',
        'annule' => 'Annulé',
    ];

    const STATUTS_COLORS = [
        'declare' => '#fef3c7',
        'en_cours' => '#dbeafe',
        'cloture' => '#dcfce7',
        'annule' => '#fee2e2',
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function declarePar()
    {
        return $this->belongsTo(User::class, 'declare_par');
    }

    // ===== SCOPES =====
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['declare', 'en_cours']);
    }

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS_COLORS[$this->statut] ?? '#f1f5f9';
    }

    public function getDureeArretAttribute(): ?int
    {
        if (!$this->date_retour) {
            return null;
        }
        return $this->date_accident->diffInDays($this->date_retour);
    }
}