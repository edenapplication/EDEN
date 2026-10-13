<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Service extends Model
{
    use Auditable;
    protected $table    = 'rh_services';
    protected $fillable = ['direction_id', 'nom', 'description'];

    public function direction() { return $this->belongsTo(Direction::class, 'direction_id'); }
    public function employes()  { return $this->hasMany(Employe::class, 'service_id'); }

     public function horaires()
    {
        return $this->hasMany(HoraireService::class, 'service_id');
    }
}