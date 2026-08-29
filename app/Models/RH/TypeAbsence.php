<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class TypeAbsence extends Model
{
    protected $table = 'rh_types_absence';

    protected $fillable = [
        'code',
        'nom',
        'est_remunere',
        'necessite_justificatif',
        'plafond_jours_annuel',
        'description',
        'actif'
    ];

    protected $casts = [
        'est_remunere' => 'boolean',
        'necessite_justificatif' => 'boolean',
        'actif' => 'boolean',
    ];

    // ===== RELATIONS =====
    public function absences()
    {
        return $this->hasMany(Absence::class, 'type_absence_id');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeRemunere($query)
    {
        return $query->where('est_remunere', true);
    }

    public function scopeNonRemunere($query)
    {
        return $query->where('est_remunere', false);
    }

    // ===== ACCESSORS =====
    public function getLabelAttribute(): string
    {
        return $this->nom . ($this->est_remunere ? ' (Rémunéré)' : ' (Non rémunéré)');
    }
}