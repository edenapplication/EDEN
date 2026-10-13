<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Tf extends Model
{
    use Auditable;
    protected $fillable = [
        'site_id', 'svg_zone_id', 'title',
        'file_path', 'color', 'status', 'is_active'
    ];

    public function site() { return $this->belongsTo(Site::class); }
    public function lots() { return $this->hasMany(Lot::class); }
    public function zoneGroupes()
{
    return $this->hasMany(ZoneGroupe::class);
}
}