<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class CertificatCessation extends Model
{
    protected $table = 'rh_certificats_cessation';

    protected $fillable = [
        'depart_id',
        'employe_id',
        'reference',
        'date_emission',
        'date_effet',
        'motif',
        'mention_speciale',
        'document_path',
        'statut',
        'valide_par',
        'date_validation',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_effet' => 'date',
        'date_validation' => 'date',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'brouillon' => 'Brouillon',
        'valide' => 'Validé',
        'delivre' => 'Délivré'
    ];

    // ===== RELATIONS =====
    public function depart()
    {
        return $this->belongsTo(Depart::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function validePar()
    {
        return $this->belongsTo(\App\Models\User::class, 'valide_par');
    }

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}