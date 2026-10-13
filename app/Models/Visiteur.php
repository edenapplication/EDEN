<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Visiteur extends Model
{
    use Auditable;
    protected $fillable = ['nom', 'numero', 'type'];

    public function visites() { return $this->hasMany(Visite::class); }
}