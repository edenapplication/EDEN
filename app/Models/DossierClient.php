<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierClient extends Model
{
    protected $table    = 'dossiers_clients';
    protected $fillable = [
        'client_id', 'nom_dossier',
        'commercial_id', 'conducteur_id',
        'facilitateur_id', 'agent_commercial_id',
        'grand_site_id', 'direction',
        'superficie_voulue', 'prix_superficie',
        'prix_technique', 'prix_morcellement','prix_logistique',
        // ✅ Ajouter les champs d'étapes
        'etape_actuelle',
        'date_implantation_prevue',
        'date_deja_implante',
        'date_dossier_technique',
        'date_morcellement',
        // ✅ AJOUTER CNI_IMAGES
        'cni_images',
    ];
    protected $casts = [
        'cni_images' => 'array',
        'date_implantation_prevue' => 'date',
        'date_deja_implante' => 'date',
        'date_dossier_technique' => 'date',
        'date_morcellement' => 'date',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'dossier_client_id')
                    ->where('statut','actif')
                    ->with(['grandSite','site','tf','bloc','lot']);
    }

    public function client()          { return $this->belongsTo(Client::class); }
    public function commercial()      { return $this->belongsTo(Commercial::class); }
    public function conducteur()      { return $this->belongsTo(Conducteur::class); }
    public function facilitateur()    { return $this->belongsTo(Facilitateur::class); }
    public function agentCommercial() { return $this->belongsTo(AgentCommercial::class, 'agent_commercial_id'); }
    public function grandSite()       { return $this->belongsTo(GrandSite::class); }
    public function paiements()       { return $this->hasMany(PaiementDossier::class); }
    public function paiementsTechniques() { return $this->hasMany(PaiementTechnique::class, 'dossier_client_id'); }
    public function paiementsMorcellements() { return $this->hasMany(PaiementMorcellement::class, 'dossier_client_id'); }
    public function paiementsLogistiques() { return $this->hasMany(PaiementLogistique::class, 'dossier_client_id'); }
    public function bons() { return $this->hasMany(BonPaiement::class, 'dossier_client_id')->orderByDesc('date_bon'); }
    public function lots() { return $this->hasMany(Lot::class); }

    // ============================================================
    // MÉTHODES DE CALCUL
    // ============================================================
    public function totalPaye(): float
    {
        return $this->paiements->sum('montant');
    }

    public function resteAPayer(): float
    {
        return max(0, ($this->prix_superficie ?? 0) - $this->totalPaye());
    }

    public function getPrixUnitaireAttribute(): float
    {
        if (!$this->superficie_voulue || $this->superficie_voulue == 0) return 0;
        return round(($this->prix_superficie ?? 0) / $this->superficie_voulue, 0);
    }

    // ============================================================
    // MÉTHODES POUR LES CNI
    // ============================================================
    public function getCniImagesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setCniImagesAttribute($value)
    {
        $this->attributes['cni_images'] = json_encode($value);
    }

    // ============================================================
    // CONFIGURATION DES ÉTAPES
    // ============================================================
    public static function etapesConfig(): array
    {
        return [
            'implantation_prevue' => [
                'label'  => 'Implantation prévue',
                'color'  => '#7c3aed',
                'bg'     => '#f5f3ff',
                'border' => '#c4b5fd',
                'icon'   => '📍',
                'champ'  => 'date_implantation_prevue',
            ],
            'deja_implante' => [
                'label'  => 'Déjà implanté',
                'color'  => '#16a34a',
                'bg'     => '#f0fdf4',
                'border' => '#86efac',
                'icon'   => '✅',
                'champ'  => 'date_deja_implante',
            ],
            'dossier_technique' => [
                'label'  => 'Dossier technique',
                'color'  => '#dc2626',
                'bg'     => '#fff1f2',
                'border' => '#fca5a5',
                'icon'   => '📁',
                'champ'  => 'date_dossier_technique',
            ],
            'morcellement' => [
                'label'  => 'Morcellement',
                'color'  => '#ca8a04',
                'bg'     => '#fefce8',
                'border' => '#fde68a',
                'icon'   => '✂️',
                'champ'  => 'date_morcellement',
            ],
        ];
    }

    public static function etapesOrdre(): array
    {
        return [
            'implantation_prevue' => 1,
            'deja_implante' => 2,
            'dossier_technique' => 3,
            'morcellement' => 4,
        ];
    }

    public function getEtapeConfigAttribute(): ?array
    {
        if (!$this->etape_actuelle) return null;
        $config = static::etapesConfig();
        $champ = $config[$this->etape_actuelle]['champ'] ?? null;
        
        if (!$champ) return null;
        
        return [
            'label' => $config[$this->etape_actuelle]['label'],
            'color' => $config[$this->etape_actuelle]['color'],
            'bg' => $config[$this->etape_actuelle]['bg'],
            'border' => $config[$this->etape_actuelle]['border'],
            'icon' => $config[$this->etape_actuelle]['icon'],
            'champ' => $champ,
            'date' => $this->$champ ? $this->$champ->format('d/m/Y') : null,
        ];
    }

    public function estEtapeAtteinte(string $etape): bool
    {
        $ordre = self::etapesOrdre();
        $ordreEtape = $ordre[$etape] ?? 0;
        $ordreActuel = $this->etape_actuelle ? ($ordre[$this->etape_actuelle] ?? 0) : 0;
        return $ordreEtape <= $ordreActuel;
    }

    public function getDateEtapeAttribute(string $etape): ?string
    {
        $config = self::etapesConfig();
        $champ = $config[$etape]['champ'] ?? null;
        if (!$champ || !$this->$champ) return null;
        return $this->$champ->format('d/m/Y');
    }
}