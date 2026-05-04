<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrandSite extends Model
{
    protected $fillable = ['nom', 'description'];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}