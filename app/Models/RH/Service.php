<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table    = 'rh_services';
    protected $fillable = ['direction_id', 'nom', 'description'];

    public function direction() { return $this->belongsTo(Direction::class, 'direction_id'); }
    public function employes()  { return $this->hasMany(Employe::class, 'service_id'); }
}