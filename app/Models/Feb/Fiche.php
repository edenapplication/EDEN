<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;

class Fiche extends Model
{
    protected $table    = 'feb_fiches';
    protected $fillable = [
    'utilisateur_id','numero_fiche','titre','description',
    'statut','vue_admin','modele_id','soumise_at',
];
    protected $casts    = ['soumise_at' => 'datetime', 'vue_admin' => 'boolean'];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'utilisateur_id'); }
    public function sections()    { return $this->hasMany(Section::class, 'fiche_id')->orderBy('ordre'); }
    public function modele()      { return $this->belongsTo(Fiche::class, 'modele_id'); }
}