<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Rapport extends Model
{
    use Auditable;
    protected $fillable = ['titre', 'description', 'filtres', 'fichier_pdf'];
    protected $casts    = ['filtres' => 'array'];
}