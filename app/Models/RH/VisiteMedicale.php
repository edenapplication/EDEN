<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VisiteMedicale extends Model
{
    protected $table = 'rh_visites_medicales';

    protected $fillable = [
        'employe_id',
        'medecin_id',
        'type',
        'date_visite',
        'medecin_nom',
        'medecin_tel',
        'etablissement',
        'aptitude',
        'restrictions',
        'observations',
        'prochaine_visite',
        'certificat_fourni',
        'certificat_path',
        'statut',
    ];

    protected $casts = [
        'date_visite' => 'date',
        'prochaine_visite' => 'date',
        'certificat_fourni' => 'boolean',
    ];

    // ===== CONSTANTES =====
    const TYPES = [
        'embauche' => 'Visite d\'embauche',
        'periodique' => 'Visite périodique',
        'reprise' => 'Visite de reprise',
        'accident' => 'Visite suite à accident',
    ];

    const APTITUDES = [
        'apte' => 'Apte',
        'apte_avec_restriction' => 'Apte avec restrictions',
        'inapte' => 'Inapte',
    ];

    const STATUTS = [
        'planifie' => 'Planifiée',
        'effectue' => 'Effectuée',
        'annule' => 'Annulée',
    ];

    const STATUTS_COLORS = [
        'planifie' => '#fef3c7',
        'effectue' => '#dcfce7',
        'annule' => '#fee2e2',
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    // ===== SCOPES =====
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAExpirer($query, $jours = 30)
    {
        return $query->where('statut', 'effectue')
            ->whereNotNull('prochaine_visite')
            ->whereBetween('prochaine_visite', [now(), now()->addDays($jours)]);
    }

    public function scopeExpirees($query)
    {
        return $query->where('statut', 'effectue')
            ->whereNotNull('prochaine_visite')
            ->where('prochaine_visite', '<', now());
    }

    // ===== ACCESSORS =====
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getAptitudeLabelAttribute(): string
    {
        return self::APTITUDES[$this->aptitude] ?? $this->aptitude;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS_COLORS[$this->statut] ?? '#f1f5f9';
    }

    public function getEstExpireeAttribute(): bool
    {
        return $this->statut === 'effectue' 
            && $this->prochaine_visite 
            && $this->prochaine_visite->isPast();
    }

    public function getJoursRestantsAttribute(): ?int
    {
        if (!$this->prochaine_visite || $this->prochaine_visite->isPast()) {
            return null;
        }
        return now()->diffInDays($this->prochaine_visite);
    }
}