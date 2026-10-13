<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Ligne extends Model
{
    use Auditable;
    protected $table    = 'feb_lignes';
    protected $fillable = ['section_id','numero_ligne','valeurs'];
    protected $casts    = ['valeurs' => 'array'];

    public function section() { return $this->belongsTo(Section::class, 'section_id'); }
}