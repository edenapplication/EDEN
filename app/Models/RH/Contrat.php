<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contrat extends Model
{
    use SoftDeletes;

    protected $table = 'rh_contrats';

    protected $fillable = [
        'employe_id',
        'type_contrat_id',
        'direction_id',
        'service_id',
        'poste_id',
        'agence_site_id',
        'numero_contrat',
        'date_debut',
        'date_fin',
        'periode_essai_jours',
        'date_fin_periode_essai',
        'est_renouvelable',
        'nb_renouvellements',
        'renouvellement_max',
        'salaire_base',
        'salaire_brut',
        'devise',
        'mode_paiement',
        'responsable_hierarchique',
        'conditions_particulieres',
        'horaires',
        'lieu_travail',
        'statut',
        'date_signature',
        'date_validation',
        'valide_par',
        'fichier_contrat_path',
        'fichier_avenant_path',
        'notes',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_fin_periode_essai' => 'date',
        'date_signature' => 'date',
        'date_validation' => 'date',
        'est_renouvelable' => 'boolean',
        'salaire_base' => 'decimal:2',
        'salaire_brut' => 'decimal:2',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'en_attente',
        'valide',
        'actif',
        'suspendu',
        'renouvele',
        'termine',
        'resilie',
        'annule'
    ];

    const STATUTS_LABELS = [
        'en_attente' => 'En attente',
        'valide' => 'Validé',
        'actif' => 'Actif',
        'suspendu' => 'Suspendu',
        'renouvele' => 'Renouvelé',
        'termine' => 'Terminé',
        'resilie' => 'Résilié',
        'annule' => 'Annulé'
    ];

    const STATUTS_COLORS = [
        'en_attente' => '#fef3c7',
        'valide' => '#dbeafe',
        'actif' => '#dcfce7',
        'suspendu' => '#fef9c3',
        'renouvele' => '#e0e7ff',
        'termine' => '#f1f5f9',
        'resilie' => '#fee2e2',
        'annule' => '#f3f4f6'
    ];

    const STATUTS_TEXT_COLORS = [
        'en_attente' => '#92400e',
        'valide' => '#1d4ed8',
        'actif' => '#15803d',
        'suspendu' => '#854d0e',
        'renouvele' => '#3730a3',
        'termine' => '#475569',
        'resilie' => '#b91c1c',
        'annule' => '#6b7280'
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function typeContrat()
    {
        return $this->belongsTo(TypeContrat::class);
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }

    public function agenceSite()
    {
        return $this->belongsTo(AgenceSite::class);
    }

    public function validePar()
    {
        return $this->belongsTo(\App\Models\User::class, 'valide_par');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['actif', 'valide']);
    }

    public function scopeTermine($query)
    {
        return $query->where('statut', 'termine');
    }

    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeAlerteFin($query, $jours = 15)
    {
        return $query->where('statut', 'actif')
                    ->whereNotNull('date_fin')
                    ->whereBetween('date_fin', [now()->startOfDay(), now()->addDays($jours)->endOfDay()]);
    }

    public function scopeAlertePeriodeEssai($query, $jours = 7)
    {
        return $query->where('statut', 'actif')
                    ->whereNotNull('date_fin_periode_essai')
                    ->whereBetween('date_fin_periode_essai', [now()->startOfDay(), now()->addDays($jours)->endOfDay()]);
    }

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS_LABELS[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS_COLORS[$this->statut] ?? '#f1f5f9';
    }

    public function getStatutTextColorAttribute(): string
    {
        return self::STATUTS_TEXT_COLORS[$this->statut] ?? '#475569';
    }

    public function getDureeAttribute(): string
    {
        if (!$this->date_fin) {
            return 'Indéterminé';
        }
        $diff = $this->date_debut->diffInMonths($this->date_fin);
        if ($diff < 1) {
            $jours = $this->date_debut->diffInDays($this->date_fin);
            return $jours . ' jour(s)';
        }
        return $diff . ' mois';
    }

    public function getEstEnPeriodeEssaiAttribute(): bool
    {
        if (!$this->periode_essai_jours) {
            return false;
        }
        if (!$this->date_fin_periode_essai) {
            return false;
        }
        return $this->date_fin_periode_essai->isFuture();
    }

    public function getJoursRestantsPeriodeEssaiAttribute(): int
    {
        if (!$this->est_en_periode_essai) {
            return 0;
        }
        return max(0, now()->diffInDays($this->date_fin_periode_essai, false));
    }

    // ===== MÉTHODES MÉTIER =====

    /**
     * Calculer automatiquement la date de fin de période d'essai
     */
    public function calculerFinPeriodeEssai(): ?Carbon
    {
        if (!$this->periode_essai_jours || $this->periode_essai_jours <= 0) {
            return null;
        }
        return $this->date_debut->copy()->addDays((int) $this->periode_essai_jours);
    }

    /**
     * Vérifier si le contrat est arrivé à échéance (CDD)
     */
    public function estEchu(): bool
    {
        if (!$this->date_fin) {
            return false;
        }
        return $this->date_fin->isPast();
    }

    /**
     * Vérifier si le contrat va bientôt expirer
     */
    public function vaExpirer($jours = 15): bool
    {
        if (!$this->date_fin) {
            return false;
        }
        return $this->date_fin->isFuture() && $this->date_fin->diffInDays(now()) <= $jours;
    }

    /**
     * Calculer la durée en jours
     */
    public function calculerDureeJours(): int
    {
        if (!$this->date_fin) {
            return 0;
        }
        return $this->date_debut->diffInDays($this->date_fin);
    }

    /**
     * Calculer la durée en mois
     */
    public function calculerDureeMois(): int
    {
        if (!$this->date_fin) {
            return 0;
        }
        return $this->date_debut->diffInMonths($this->date_fin);
    }

    /**
     * Générer un numéro de contrat unique
     */
    public static function genererNumeroContrat(int $employeId, \DateTime $dateDebut): string
    {
        return 'CT-' . $dateDebut->format('Y') . '-' . str_pad($employeId, 4, '0', STR_PAD_LEFT) . '-' . now()->format('His');
    }

    /**
     * Vérifier si le contrat peut être renouvelé
     */
    public function peutEtreRenouvele(): bool
    {
        if (!$this->est_renouvelable) {
            return false;
        }
        if ($this->renouvellement_max && $this->nb_renouvellements >= $this->renouvellement_max) {
            return false;
        }
        return true;
    }

    /**
     * Créer un renouvellement de contrat
     */
    public function renouveler(array $data): Contrat
    {
        $this->statut = 'termine';
        $this->save();

        $nouveauContrat = new Contrat();
        $nouveauContrat->fill(array_merge(
            $this->toArray(),
            $data,
            [
                'date_debut' => $data['date_debut'] ?? $this->date_fin?->addDay() ?? now(),
                'date_fin' => $data['date_fin'] ?? null,
                'nb_renouvellements' => $this->nb_renouvellements + 1,
                'statut' => 'valide',
                'numero_contrat' => self::genererNumeroContrat($this->employe_id, $this->date_debut),
            ]
        ));
        $nouveauContrat->save();

        return $nouveauContrat;
    }
}