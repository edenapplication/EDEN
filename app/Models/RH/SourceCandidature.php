<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class SourceCandidature extends Model
{
    use Auditable;
    protected $table = 'rh_sources_candidature';

    protected $fillable = [
        'code',
        'nom',
        'description',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // ===== RELATIONS =====
    public function candidats()
    {
        return $this->hasMany(Candidat::class, 'source_id');
    }

    // ===== SCOPES =====
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}