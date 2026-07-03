<?php
namespace App\Models\Feb;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Utilisateur extends Model
{
    protected $table    = 'feb_utilisateurs';
    protected $fillable = ['identifiant','password','nom','prenom','poste','agence_id','actif'];
    protected $hidden   = ['password'];

    public function agence()  { return $this->belongsTo(Agence::class, 'agence_id'); }
    public function fiches()  { return $this->hasMany(Fiche::class, 'utilisateur_id'); }

    public function verifierPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }
}