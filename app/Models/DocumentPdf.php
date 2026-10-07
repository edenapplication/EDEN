<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPdf extends Model
{
    protected $table = 'documents_pdf';

    protected $fillable = [
        'type',
        'date_semaine',
        'nom_fichier',
        'chemin_fichier',
        'nb_lignes',
        'nb_lots',
        'superficie_totale',
        'user_id',
    ];

    protected $casts = [
        'date_semaine'      => 'date',
        'superficie_totale' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->chemin_fichier);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'programmation_initiale' => '📝 Programmation initiale',
            'programmation_active'   => '🎯 Programmation active',
            'programmation_finale'   => '✅ Programmation finalisée',
            default                  => 'Document',
        };
    }
}