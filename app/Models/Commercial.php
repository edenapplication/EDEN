<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commercial extends Model
{
    protected $table = 'commerciaux';
    protected $fillable = [
    'name',
    'phone',
    'agence'
];

public function lots()
{
    return $this->hasMany(\App\Models\Lot::class);
}

}
