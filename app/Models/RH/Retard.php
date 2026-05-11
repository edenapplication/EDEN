<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Retard extends Model
{
    protected $table = 'rh_retards';
    protected $fillable = ['employe_id','date','heure_arrivee','minutes_retard','montant_deduction','justification','justifie'];

    protected $casts = ['date' => 'date', 'justifie' => 'boolean'];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }
}