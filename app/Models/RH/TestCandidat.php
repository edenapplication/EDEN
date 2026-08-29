<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class TestCandidat extends Model
{
    protected $table = 'rh_tests_candidats';

    protected $fillable = [
        'candidat_id',
        'type_test',
        'date_test',
        'note',
        'resultats',
        'appreciation',
        'fichier_path',
    ];

    protected $casts = [
        'date_test' => 'date',
        'note' => 'decimal:2',
    ];

    // ===== RELATIONS =====
    public function candidat()
    {
        return $this->belongsTo(Candidat::class, 'candidat_id');
    }

    // ===== SCOPES =====
    public function scopeReussi($query)
    {
        return $query->where('note', '>=', 10);
    }

    // ===== ACCESSORS =====
    public function getNoteLabelAttribute(): string
    {
        if (!$this->note) return '-';
        return $this->note . '/20';
    }

    public function getEstReussiAttribute(): bool
    {
        return $this->note && $this->note >= 10;
    }
}