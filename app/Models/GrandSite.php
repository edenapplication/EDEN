<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GrandSite extends Model
{
    use Auditable;
    protected $fillable = ['nom', 'description'];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}