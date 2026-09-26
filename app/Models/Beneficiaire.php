<?php
// app/Models/Beneficiaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficiaire extends Model
{
    protected $table = 'beneficiaires';

    protected $fillable = [
        'dossier_client_id',
        'nom',
        'telephone',
        'cni_path',
        'lots_texte',
        'superficie_attribuee',
        'notes',
        // ✅ Étapes
        'implantation_prevue',
        'deja_implante',
        'dossier_technique',
        'morcellement',
        'etape_actuelle',
    ];

    protected $casts = [
        'superficie_attribuee' => 'float',
        'implantation_prevue'  => 'date',
        'deja_implante'        => 'date',
        'dossier_technique'    => 'date',
        'morcellement'         => 'date',
    ];

    // ════════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════════
    public function dossier()
    {
        return $this->belongsTo(DossierClient::class, 'dossier_client_id');
    }

    // ✅ Affectations de lots liées au bénéficiaire
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'beneficiaire_id');
    }

    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════
    public function getCniUrlAttribute(): ?string
    {
        return $this->cni_path ? asset('storage/' . $this->cni_path) : null;
    }

    public function getSuperficieFormateeAttribute(): string
    {
        return number_format($this->superficie_attribuee, 0, ',', ' ') . ' m²';
    }

    // ════════════════════════════════════════════════════════════
    // ÉTAPES — mêmes méthodes que DossierClient
    // ════════════════════════════════════════════════════════════
    public static function etapesConfig(): array
    {
        return [
            'implantation_prevue' => [
                'champ' => 'implantation_prevue',
                'label' => 'Implantation prévue',
                'icon'  => '📍',
                'color' => '#7c3aed',
                'bg'    => '#f5f3ff',
            ],
            'deja_implante' => [
                'champ' => 'deja_implante',
                'label' => 'Déjà implanté',
                'icon'  => '✅',
                'color' => '#16a34a',
                'bg'    => '#f0fdf4',
            ],
            'dossier_technique' => [
                'champ' => 'dossier_technique',
                'label' => 'Dossier technique',
                'icon'  => '📁',
                'color' => '#dc2626',
                'bg'    => '#fff1f2',
            ],
            'morcellement' => [
                'champ' => 'morcellement',
                'label' => 'Morcellement',
                'icon'  => '✂️',
                'color' => '#ca8a04',
                'bg'    => '#fefce8',
            ],
        ];
    }

    public static function etapesOrdre(): array
    {
        return [
            'implantation_prevue' => 1,
            'deja_implante'       => 2,
            'dossier_technique'   => 3,
            'morcellement'        => 4,
        ];
    }
}