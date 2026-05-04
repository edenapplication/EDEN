<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierFile extends Model
{
    protected $fillable = [
        'dossier_id',
        'name',
        'file_path',
        'type'
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTechnique::class, 'dossier_id');
    }
}