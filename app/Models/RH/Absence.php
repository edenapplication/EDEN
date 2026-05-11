<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $table = 'rh_absences';
    protected $fillable = [
        'employe_id','reference','date_debut','date_fin','date_reprise',
        'nombre_jours','motif','type_journee','type_absence',
        'justificatif_fourni','justificatif_path','observations',
        'statut','visa_rh',
    ];

    protected $casts = [
        'date_debut'        => 'date',
        'date_fin'          => 'date',
        'date_reprise'      => 'date',
        'justificatif_fourni'=> 'boolean',
    ];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }

    public function calculerNombreJours(): int
    {
        $diff = $this->date_debut->diffInDays($this->date_fin) + 1;
        return $this->type_journee === 'demi-journée' ? (int)ceil($diff / 2) : $diff;
    }
}