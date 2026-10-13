<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Colonne extends Model
{
    use Auditable;
    protected $table    = 'feb_colonnes';
    protected $fillable = ['libelle','description','ordre','actif'];
}