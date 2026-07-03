<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;

class Colonne extends Model
{
    protected $table    = 'feb_colonnes';
    protected $fillable = ['libelle','description','ordre','actif'];
}