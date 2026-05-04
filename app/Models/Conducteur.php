<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conducteur extends Model
{
    protected $fillable = ['nom', 'numero'];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }
}
