<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Sanction extends Model
{
    protected $table = 'rh_sanctions';
    protected $fillable = ['employe_id','date','motif','type','duree_jours','montant','description','statut'];

    protected $casts = ['date' => 'date'];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }
}