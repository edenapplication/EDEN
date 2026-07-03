<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $table    = 'feb_agences';
    protected $fillable = ['nom','code','localite','actif'];

    public function utilisateurs() { return $this->hasMany(Utilisateur::class, 'agence_id'); }
}