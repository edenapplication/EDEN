<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class AgentCommercial extends Model
{
    use Auditable;
    protected $table    = 'agents_commerciaux';
    protected $fillable = ['nom', 'numero'];

    public function dossiers() { return $this->hasMany(DossierClient::class, 'agent_commercial_id'); }
}