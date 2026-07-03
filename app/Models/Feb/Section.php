<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table    = 'feb_sections';
    protected $fillable = ['fiche_id','titre','ordre'];

    public function fiche()   { return $this->belongsTo(Fiche::class, 'fiche_id'); }
    public function colonnes(){ return $this->belongsToMany(Colonne::class, 'feb_section_colonnes', 'section_id', 'colonne_id')->withPivot('ordre')->orderByPivot('ordre'); }
    public function lignes()  { return $this->hasMany(Ligne::class, 'section_id')->orderBy('numero_ligne'); }
}