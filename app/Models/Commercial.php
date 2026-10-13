<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Commercial extends Model
{
    use Auditable;
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
