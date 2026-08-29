<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employe extends Model
{
    protected $table = 'rh_employes';
    
    protected $fillable = [
        'matricule',
        'photo_path',
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'nationalite',          // ✅ NOUVEAU
        'numero_cni',
        'niu',
        'numero_cnps',          // ✅ NOUVEAU
        'date_affiliation_cnps',// ✅ NOUVEAU
        'centre_cnps',          // ✅ NOUVEAU
        'situation_affiliation_cnps', // ✅ NOUVEAU
        'origines',
        'situation_matrimoniale',
        'nb_enfants',
        'etat_sante',
        'telephone',
        'email',                // ✅ NOUVEAU
        'adresse',
        'personne_a_contacter',
        'tel_urgence',
        'direction_id',
        'service_id',
        'poste_id',
        'agence_site_id',       // ✅ NOUVEAU
        'intitule_poste',
        'type_contrat',
        'categorie',
        'niveau_chelon_id',     // ✅ NOUVEAU
        'date_integration',
        'date_prise_fonction',  // ✅ NOUVEAU
        'date_fin_periode_essai',// ✅ NOUVEAU
        'date_sortie',
        'cause_depart',
        'vague_paiement',
        'mode_paiement',        // ✅ NOUVEAU
        'niveau_academique',
        'specialite_academique',
        'diplome_recrutement',
        'exp_poste_precedent',
        'entreprise_precedente',
        'duree_exp_precedente',
        'salaire_base',
        'solde_conges',
        'conges_pris',
        'actif',
        'notes',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_integration' => 'date',
        'date_sortie' => 'date',
        'date_prise_fonction' => 'date',
        'date_fin_periode_essai' => 'date',
        'date_affiliation_cnps' => 'date',
        'actif' => 'boolean',
    ];

    // ===== RELATIONS EXISTANTES =====
    public function direction()
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }

    // ===== NOUVELLES RELATIONS =====
    public function agenceSite()
    {
        return $this->belongsTo(AgenceSite::class, 'agence_site_id');
    }

    public function niveauCheleon()
    {
        return $this->belongsTo(NiveauCheleon::class, 'niveau_chelon_id');
    }

    public function responsableHierarchique()
    {
        return $this->belongsTo(Employe::class, 'responsable_hierarchique_id');
    }

    public function subordonnes()
    {
        return $this->hasMany(Employe::class, 'responsable_hierarchique_id');
    }

    // ===== RELATIONS EXISTANTES (inchangées) =====
    public function bulletins()
    {
        return $this->hasMany(BulletinPaie::class, 'employe_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'employe_id');
    }

    public function prets()
    {
        return $this->hasMany(Pret::class, 'employe_id');
    }

    public function heuresSup()
    {
        return $this->hasMany(HeureSup::class, 'employe_id');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class, 'employe_id');
    }

    public function retards()
    {
        return $this->hasMany(Retard::class, 'employe_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeDocument::class, 'employe_id')->orderBy('created_at', 'desc');
    }

    // ===== ACCESSORS =====
    public function getNomCompletAttribute(): string
    {
        return $this->nom . ' ' . $this->prenom;
    }

    public function getAncienneteAttribute(): string
    {
        return $this->date_integration?->diffForHumans(now(), true) ?? '-';
    }

    public function getSoldeCongesRestantAttribute(): int
    {
        return max(0, ($this->solde_conges ?? 0) - ($this->conges_pris ?? 0));
    }

    // ===== MÉTHODES =====
    public static function genererMatricule(int $id, ?string $dateIntegration = null): string
    {
        $date = $dateIntegration
            ? Carbon::parse($dateIntegration)
            : now();

        $mois  = $date->format('m');
        $annee = $date->format('y');

        return "EDG_{$mois}_{$annee}_" . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    public function regenererMatricule(): string
    {
        return self::genererMatricule($this->id, $this->date_integration?->format('Y-m-d'));
    }

    // ===== RELATION AVEC LES CONTRATS =====
public function contrats()
{
    return $this->hasMany(Contrat::class, 'employe_id')->orderBy('date_debut', 'desc');
}

public function contratActif()
{
    return $this->hasOne(Contrat::class, 'employe_id')->where('statut', 'actif');
}

public function dernierContrat()
{
    return $this->hasOne(Contrat::class, 'employe_id')->orderBy('date_debut', 'desc');
}

// ===== RELATION CNPS =====
public function cnpsAffiliation()
{
    return $this->hasOne(CnpsAffiliation::class, 'employe_id');
}

public function cnpsLignesDeclaration()
{
    return $this->hasMany(CnpsLigneDeclaration::class, 'employe_id');
}

// ===== RELATIONS DÉPART =====
public function depart()
{
    return $this->hasOne(Depart::class)->latest();
}

public function departs()
{
    return $this->hasMany(Depart::class);
}

public function soldeToutCompte()
{
    return $this->hasOne(SoldeToutCompte::class)->latest();
}

public function certificatCessation()
{
    return $this->hasOne(CertificatCessation::class)->latest();
}

public function getEstEnCoursDeDepartAttribute(): bool
{
    return $this->depart()->whereIn('statut', ['en_attente', 'valide', 'en_cours'])->exists();
}

}