<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
class DossierFile extends Model
{
    use Auditable;
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