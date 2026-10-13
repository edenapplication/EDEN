<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Facilitateur extends Model
{
    use Auditable;
    protected $fillable = ['nom', 'numero'];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }
}
