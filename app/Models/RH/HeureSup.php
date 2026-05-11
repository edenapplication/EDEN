<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class HeureSup extends Model
{
    protected $table = 'rh_heures_sup';
    protected $fillable = ['employe_id','date','nb_heures','taux_majoration','montant','justification','valide'];

    protected $casts = ['date' => 'date', 'valide' => 'boolean'];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }

    public function calculerMontant(float $salaireHeure): float
    {
        return round($this->nb_heures * $salaireHeure * $this->taux_majoration, 2);
    }
}