<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = ['titre', 'description', 'filtres', 'fichier_pdf'];
    protected $casts    = ['filtres' => 'array'];
}