<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visiteur extends Model
{
    protected $fillable = ['nom', 'numero', 'type'];

    public function visites() { return $this->hasMany(Visite::class); }
}