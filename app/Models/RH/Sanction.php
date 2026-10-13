<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Sanction extends Model
{
    use Auditable;
    protected $table = 'rh_sanctions';
    protected $fillable = ['employe_id','date','motif','type','duree_jours','montant','description','statut'];

    protected $casts = ['date' => 'date'];

    public function employe() { return $this->belongsTo(Employe::class, 'employe_id'); }
}