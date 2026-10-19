<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportLogistique extends Model
{
    protected $table = 'rapports_logistiques';

    protected $fillable = [
        'titre',
        'note',
        'type',
        'chemin_fichier',
        'nom_fichier',
        'nb_dossiers',
        'nb_beneficiaires',
        'total_prix',
        'total_paye',
        'total_reste',
        'total_global_encaisse',
        'frais_transport',        // ✅ Ajout
    'reste_a_reverser',       // ✅ Ajout
        'filtres',
        'user_id',
    ];

    protected $casts = [
        'filtres' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->chemin_fichier) return null;
        return asset('storage/' . $this->chemin_fichier);
    }

    /**
 * ✅ Calcule l'activité comptable à la volée selon les filtres sauvegardés
 */
public function calculerActiviteComptable(): array
{
    $filtres = $this->filtres ?? [];

    $comptableReferences = \App\Models\User::where('role', 'comptable')
        ->where('actif', true)
        ->pluck('reference')
        ->filter()
        ->toArray();

    $query = \App\Models\PaiementLogistique::query()
        ->whereHas('dossier.beneficiaires', function ($q) use ($filtres) {
            if (!empty($filtres['date_implantation'])) {
                $q->whereDate('implantation_prevue', $filtres['date_implantation']);
            } elseif (!empty($filtres['date_implantation_debut']) || !empty($filtres['date_implantation_fin'])) {
                if (!empty($filtres['date_implantation_debut'])) {
                    $q->whereDate('implantation_prevue', '>=', $filtres['date_implantation_debut']);
                }
                if (!empty($filtres['date_implantation_fin'])) {
                    $q->whereDate('implantation_prevue', '<=', $filtres['date_implantation_fin']);
                }
            }
        });

    if (!empty($filtres['grand_site_id'])) {
        $query->whereHas('dossier', function ($q) use ($filtres) {
            $q->where('grand_site_id', $filtres['grand_site_id']);
        });
    }

    if (!empty($filtres['q'])) {
        $q = $filtres['q'];
        $query->whereHas('dossier', function ($dq) use ($q) {
            $dq->where('nom_dossier', 'LIKE', "%{$q}%")
               ->orWhereHas('client', fn($cq) =>
                   $cq->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('phone', 'LIKE', "%{$q}%"));
        });
    }

    $totalComptable = (clone $query)->whereIn('user_reference', $comptableReferences)->sum('montant');
    $nbComptable    = (clone $query)->whereIn('user_reference', $comptableReferences)->count();

    $totalAutres = (clone $query)->where(function ($q) use ($comptableReferences) {
        $q->whereNotIn('user_reference', $comptableReferences)->orWhereNull('user_reference');
    })->sum('montant');

    $nbAutres = (clone $query)->where(function ($q) use ($comptableReferences) {
        $q->whereNotIn('user_reference', $comptableReferences)->orWhereNull('user_reference');
    })->count();

    return [
        'total_comptable' => $totalComptable,
        'nb_comptable'    => $nbComptable,
        'total_autres'    => $totalAutres,
        'nb_autres'       => $nbAutres,
        'total_global'    => $totalComptable + $totalAutres,
        'frais_transport' => $this->frais_transport,
        'reste_a_reverser'=> $this->reste_a_reverser,
    ];
}

}