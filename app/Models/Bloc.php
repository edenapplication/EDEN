<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bloc extends Model
{
    protected $table    = 'blocs';
    protected $fillable = ['grand_site_id','site_id','tf_id','code','description','actif'];

    public function grandSite()  { return $this->belongsTo(GrandSite::class, 'grand_site_id'); }
    public function site()       { return $this->belongsTo(Site::class, 'site_id'); }
    public function tf()         { return $this->belongsTo(Tf::class, 'tf_id'); }
    public function lots()       { return $this->hasMany(LotAffectation::class, 'bloc_id'); }
    public function lotsDisponibles() { return $this->hasMany(LotAffectation::class, 'bloc_id')->where('disponible', true); }
}