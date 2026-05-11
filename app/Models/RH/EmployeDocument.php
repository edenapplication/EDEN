<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class EmployeDocument extends Model
{
    protected $table    = 'rh_employe_documents';
    protected $fillable = [
        'employe_id', 'nom', 'type',
        'fichier_path', 'fichier_nom', 'fichier_taille', 'description',
    ];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }

    public function getTailleFormatteeAttribute(): string
    {
        if (!$this->fichier_taille) return '-';
        $kb = $this->fichier_taille / 1024;
        return $kb >= 1024 ? round($kb / 1024, 1).' MB' : round($kb, 0).' KB';
    }
}