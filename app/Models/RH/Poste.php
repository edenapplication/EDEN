<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Poste extends Model
{
    use Auditable;
    protected $table    = 'rh_postes';
    protected $fillable = ['direction_id', 'service_id', 'code', 'intitule'];

    public function direction() { return $this->belongsTo(Direction::class, 'direction_id'); }
    public function service()   { return $this->belongsTo(Service::class, 'service_id'); }
    public function employes()  { return $this->hasMany(Employe::class, 'poste_id'); }
}