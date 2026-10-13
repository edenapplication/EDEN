<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Agence extends Model
{
    use Auditable;
    protected $table    = 'feb_agences';
    protected $fillable = ['nom','code','localite','actif'];

    public function utilisateurs() { return $this->hasMany(Utilisateur::class, 'agence_id'); }
}