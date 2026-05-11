<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Pret extends Model
{
    protected $table = 'rh_prets';
    protected $fillable = [
        'employe_id','type','montant','duree_mois','mensualite',
        'montant_rembourse','date_debut','date_fin_prevue','statut','notes',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin_prevue' => 'date',
    ];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }

    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->montant - $this->montant_rembourse);
    }

    public function calculerMensualite(): float
    {
        if (!$this->duree_mois || $this->duree_mois === 0) return $this->montant;
        return round($this->montant / $this->duree_mois, 2);
    }
}