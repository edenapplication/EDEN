<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommercial extends Model
{
    protected $table    = 'agents_commerciaux';
    protected $fillable = ['nom', 'numero'];

    public function dossiers() { return $this->hasMany(DossierClient::class, 'agent_commercial_id'); }
}