<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidat extends Model
{
    use SoftDeletes;

    protected $table = 'rh_candidats';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'adresse',
        'poste_demande',
        'niveau_academique',
        'specialite',
        'annees_experience',
        'dernier_poste',
        'dernier_employeur',
        'competences',
        'source_id',
        'date_candidature',
        'salaire_souhaite',
        'disponibilite',
        'statut',
        'cv_path',
        'lettre_motivation_path',
        'diplomes_path',
        'notes',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_candidature' => 'date',
        'disponibilite' => 'date',
        'salaire_souhaite' => 'decimal:2',
        'annees_experience' => 'integer',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'recu' => 'Candidature reçue',
        'preselectionne' => 'Présélectionné',
        'entretien_rh' => 'Entretien RH',
        'entretien_hierarchique' => 'Entretien hiérarchique',
        'test' => 'Phase de test',
        'valide' => 'Validé',
        'embauche' => 'Embauché',
        'rejete' => 'Rejeté'
    ];

    const STATUTS_COLORS = [
        'recu' => '#f1f5f9',
        'preselectionne' => '#dbeafe',
        'entretien_rh' => '#fef3c7',
        'entretien_hierarchique' => '#fef3c7',
        'test' => '#fef3c7',
        'valide' => '#dbeafe',
        'embauche' => '#dcfce7',
        'rejete' => '#fee2e2'
    ];

    const STATUTS_TEXT_COLORS = [
        'recu' => '#475569',
        'preselectionne' => '#1d4ed8',
        'entretien_rh' => '#92400e',
        'entretien_hierarchique' => '#92400e',
        'test' => '#92400e',
        'valide' => '#1d4ed8',
        'embauche' => '#15803d',
        'rejete' => '#b91c1c'
    ];

    const STATUTS_ICONS = [
        'recu' => 'bi-inbox',
        'preselectionne' => 'bi-check-circle',
        'entretien_rh' => 'bi-person-chat',
        'entretien_hierarchique' => 'bi-people',
        'test' => 'bi-clipboard-check',
        'valide' => 'bi-check2-all',
        'embauche' => 'bi-person-plus',
        'rejete' => 'bi-x-circle'
    ];

    // ===== RELATIONS =====
    public function source()
    {
        return $this->belongsTo(SourceCandidature::class, 'source_id');
    }

    public function entretiens()
    {
        return $this->hasMany(Entretien::class, 'candidat_id')->orderBy('date_entretien', 'desc');
    }

    public function tests()
    {
        return $this->hasMany(TestCandidat::class, 'candidat_id')->orderBy('date_test', 'desc');
    }

    public function dernierEntretien()
    {
        return $this->hasOne(Entretien::class, 'candidat_id')->orderBy('date_entretien', 'desc');
    }

    // ===== SCOPES =====
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeActifs($query)
    {
        return $query->whereNotIn('statut', ['embauche', 'rejete']);
    }

    public function scopeRecherche($query, $search)
    {
        return $query->where('nom', 'LIKE', "%{$search}%")
            ->orWhere('prenom', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('telephone', 'LIKE', "%{$search}%");
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

    public function getStatutTextColorAttribute(): string
    {
        return self::STATUTS_TEXT_COLORS[$this->statut] ?? '#475569';
    }

    public function getStatutIconAttribute(): string
    {
        return self::STATUTS_ICONS[$this->statut] ?? 'bi-question-circle';
    }

    public function getNomCompletAttribute(): string
    {
        return $this->nom . ' ' . $this->prenom;
    }

    public function getProgressionAttribute(): int
    {
        $steps = array_keys(self::STATUTS);
        $current = array_search($this->statut, $steps);
        $total = count($steps) - 1;
        return $total > 0 ? round(($current / $total) * 100) : 0;
    }

    // ===== MÉTHODES =====
    public function peutPasserA($nouveauStatut): bool
    {
        $steps = array_keys(self::STATUTS);
        $current = array_search($this->statut, $steps);
        $target = array_search($nouveauStatut, $steps);
        
        // On ne peut avancer que d'un cran ou reculer
        return abs($target - $current) <= 1 || $nouveauStatut === 'rejete';
    }

    public function avancerStatut(): bool
    {
        $steps = array_keys(self::STATUTS);
        $current = array_search($this->statut, $steps);
        
        if ($current < count($steps) - 1) {
            $next = $steps[$current + 1];
            if ($this->peutPasserA($next)) {
                $this->statut = $next;
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function reculerStatut(): bool
    {
        $steps = array_keys(self::STATUTS);
        $current = array_search($this->statut, $steps);
        
        if ($current > 0) {
            $prev = $steps[$current - 1];
            $this->statut = $prev;
            $this->save();
            return true;
        }
        return false;
    }

    public function estEmbauchable(): bool
    {
        return $this->statut === 'valide';
    }

    public function creerEmploye(): ?Employe
    {
        if (!$this->estEmbauchable()) {
            return null;
        }

        $employe = Employe::create([
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'sexe' => $this->sexe,
            'date_naissance' => $this->date_naissance,
            'lieu_naissance' => $this->lieu_naissance,
            'nationalite' => $this->nationalite,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'intitule_poste' => $this->poste_demande,
            'niveau_academique' => $this->niveau_academique,
            'specialite_academique' => $this->specialite,
            'matricule' => 'TEMP', // Sera régénéré
            'actif' => true,
        ]);

        // Générer le matricule
        $employe->matricule = Employe::genererMatricule($employe->id, now());
        $employe->save();

        // Mettre à jour le statut du candidat
        $this->statut = 'embauche';
        $this->save();

        return $employe;
    }
}