<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $table    = 'rh_directions';
    protected $fillable = ['code', 'nom', 'description'];

    public function services()  { return $this->hasMany(Service::class, 'direction_id'); }
    public function postes()    { return $this->hasMany(Poste::class, 'direction_id'); }
    public function employes()  { return $this->hasMany(Employe::class, 'direction_id'); }
}