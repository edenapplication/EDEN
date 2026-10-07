<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    protected $table = 'affectations';

    protected $fillable = [
        'grand_site_id','site_id','tf_id','bloc_id',
        'lot_affectation_id','client_id','dossier_client_id',
        'date_affectation','statut','notes','beneficiaire_id',
        'statut_acceptation', 'motif_refus',
    'accepte_le', 'refuse_le',
    'accepte_par', 'refuse_par',
    'date_acceptation', 'date_refus', 
    'date_implantation', 'geometre_id',
    'heure_implantation', 'frais_logistique_paye', 'statut_presence',
    ];

    protected $casts = ['date_affectation' => 'date',
 'accepte_le'       => 'datetime',
    'refuse_le'        => 'datetime',
    'date_acceptation' => 'date',
    'date_refus'       => 'date',
     'date_implantation' => 'datetime',
     'frais_logistique_paye' => 'boolean',
];

    public function grandSite() { return $this->belongsTo(GrandSite::class, 'grand_site_id'); }
    public function site()      { return $this->belongsTo(Site::class, 'site_id'); }
    public function tf()        { return $this->belongsTo(Tf::class, 'tf_id'); }
    public function bloc()      { return $this->belongsTo(Bloc::class, 'bloc_id'); }
    public function lot()       { return $this->belongsTo(LotAffectation::class, 'lot_affectation_id'); }
    public function client()    { return $this->belongsTo(Client::class, 'client_id'); }
    public function dossier()   { return $this->belongsTo(DossierClient::class, 'dossier_client_id'); }
    public function beneficiaire() { return $this->belongsTo(Beneficiaire::class, 'beneficiaire_id'); }
    public function acceptePar() { return $this->belongsTo(User::class, 'accepte_par'); }
public function refusePar()  { return $this->belongsTo(User::class, 'refuse_par'); }

        // ── Helpers ──
    public function isEnAttente(): bool { return $this->statut_acceptation === 'en_attente'; }
    public function isAccepte():   bool { return $this->statut_acceptation === 'accepte'; }
    public function isRefuse():    bool { return $this->statut_acceptation === 'refuse'; }

    public function getStatutAcceptationLabelAttribute(): string
    {
        return match($this->statut_acceptation) {
            'accepte'    => '✅ Accepté',
            'refuse'     => '❌ Refusé',
            default      => '⏳ En attente',
        };
    }

    public function getStatutAcceptationColorAttribute(): string
    {
        return match($this->statut_acceptation) {
            'accepte'    => '#16a34a',
            'refuse'     => '#dc2626',
            default      => '#f59e0b',
        };
    }

    public function geometre()
{
    return $this->belongsTo(\App\Models\User::class, 'geometre_id');
}

// ── Statut de présence ──
public function getStatutPresenceLabelAttribute(): string
{
    return match($this->statut_presence) {
        'present' => '✅ Présent',
        'retard'  => '⏰ Retard',
        'absent'  => '❌ Absent',
        default   => '⏳ En attente',
    };
}

public function getStatutPresenceColorAttribute(): string
{
    return match($this->statut_presence) {
        'present' => '#16a34a',
        'retard'  => '#f59e0b',
        'absent'  => '#dc2626',
        default   => '#64748b',
    };
}

}