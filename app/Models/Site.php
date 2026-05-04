<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'grand_site_id', 'name', 'description', 'svg_path', 'is_active'
    ];

    public function grandSite() { return $this->belongsTo(GrandSite::class); }
    public function tfs()       { return $this->hasMany(Tf::class); }
}