<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotAffectation extends Model
{
    protected $table    = 'lots_affectation';
    protected $fillable = ['grand_site_id','site_id','tf_id','bloc_id','numero','superficie','disponible','actif'];
    protected $casts    = ['disponible' => 'boolean', 'actif' => 'boolean'];

    public function bloc()       { return $this->belongsTo(Bloc::class, 'bloc_id'); }
    public function grandSite()  { return $this->belongsTo(GrandSite::class, 'grand_site_id'); }
    public function site()       { return $this->belongsTo(Site::class, 'site_id'); }
    public function tf()         { return $this->belongsTo(Tf::class, 'tf_id'); }
    public function affectation(){ return $this->hasOne(Affectation::class, 'lot_affectation_id')->where('statut','actif'); }

    public function getLabelAttribute(): string
    {
        return 'Lot ' . $this->numero . ($this->superficie ? ' (' . number_format($this->superficie,0,',','') . ' m²)' : '');
    }
}