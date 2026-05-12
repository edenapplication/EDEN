<?php
namespace App\Models\RH;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    protected $table = 'rh_employes';
    protected $fillable = [
        'matricule','nom','prenom','sexe','date_naissance','lieu_naissance',
        'numero_cni','niu','origines','situation_matrimoniale','nb_enfants','etat_sante',
        'telephone','adresse','personne_a_contacter','tel_urgence',
        'direction_id','service_id','poste_id','intitule_poste',
        'type_contrat','categorie','date_integration','date_sortie','cause_depart','vague_paiement',
        'niveau_academique','specialite_academique','diplome_recrutement',
        'exp_poste_precedent','entreprise_precedente','duree_exp_precedente',
        'salaire_base','solde_conges','conges_pris','actif','notes',
    ];

    protected $casts = [
        'date_naissance'   => 'date',
        'date_integration' => 'date',
        'date_sortie'      => 'date',
        'actif'            => 'boolean',
    ];

    public function direction()  { return $this->belongsTo(Direction::class, 'direction_id'); }
    public function service()    { return $this->belongsTo(Service::class, 'service_id'); }
    public function poste()      { return $this->belongsTo(Poste::class, 'poste_id'); }
    public function bulletins()  { return $this->hasMany(BulletinPaie::class, 'employe_id'); }
    public function absences()   { return $this->hasMany(Absence::class, 'employe_id'); }
    public function prets()      { return $this->hasMany(Pret::class, 'employe_id'); }
    public function heuresSup()  { return $this->hasMany(HeureSup::class, 'employe_id'); }
    public function sanctions()  { return $this->hasMany(Sanction::class, 'employe_id'); }
    public function retards()    { return $this->hasMany(Retard::class, 'employe_id'); }
    public function documents()  { return $this->hasMany(EmployeDocument::class, 'employe_id')->orderBy('created_at', 'desc'); }

    public function getNomCompletAttribute(): string
    {
        return $this->nom . ' ' . $this->prenom;
    }

    public function getAncienneteAttribute(): string
    {
        return $this->date_integration->diffForHumans(now(), true);
    }

    public function getSoldeCongesRestantAttribute(): int
    {
        return max(0, $this->solde_conges - $this->conges_pris);
    }

    public static function genererMatricule(): string
{
    $mois   = now()->format('m');   // 04
    $annee  = now()->format('y');   // 26
    $prefix = "EDG_{$mois}_{$annee}_";

    // Trouver le dernier matricule de ce mois/année
    $dernier = static::where('matricule', 'LIKE', $prefix . '%')
                      ->orderByDesc('id')
                      ->value('matricule');

    if (!$dernier) {
        $num = 1;
    } else {
        // Extraire le numéro : EDG_04_26_0003 → 3
        $num = (int) substr($dernier, strrpos($dernier, '_') + 1) + 1;
    }

    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}

}