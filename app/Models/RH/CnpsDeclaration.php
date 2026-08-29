<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CnpsDeclaration extends Model
{
    protected $table = 'rh_cnps_declarations';

    protected $fillable = [
        'periode',
        'mois',
        'annee',
        'reference',
        'date_declaration',
        'date_echeance',
        'date_paiement',
        'total_salaire_soumis',
        'total_cotisation_salariale',
        'total_cotisation_patronale',
        'total_cnps',
        'total_penalites',
        'statut',
        'fichier_dipe_path',
        'fichier_facture_path',
        'fichier_justificatif_path',
        'valide_par',
        'date_validation',
        'observations',
    ];

    protected $casts = [
        'date_declaration' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'date_validation' => 'date',
        'total_salaire_soumis' => 'decimal:2',
        'total_cotisation_salariale' => 'decimal:2',
        'total_cotisation_patronale' => 'decimal:2',
        'total_cnps' => 'decimal:2',
        'total_penalites' => 'decimal:2',
    ];

    // ===== CONSTANTES =====
    const STATUTS = [
        'a_declarer' => 'À déclarer',
        'declare' => 'Déclaré',
        'facture_recue' => 'Facture reçue',
        'paye' => 'Payé',
        'justifie' => 'Justifié'
    ];

    const STATUTS_COLORS = [
        'a_declarer' => '#fef3c7',
        'declare' => '#dbeafe',
        'facture_recue' => '#e0e7ff',
        'paye' => '#dcfce7',
        'justifie' => '#f1f5f9'
    ];

    const STATUTS_TEXT_COLORS = [
        'a_declarer' => '#92400e',
        'declare' => '#1d4ed8',
        'facture_recue' => '#3730a3',
        'paye' => '#15803d',
        'justifie' => '#475569'
    ];

    const STATUTS_ORDER = [
        'a_declarer' => 1,
        'declare' => 2,
        'facture_recue' => 3,
        'paye' => 4,
        'justifie' => 5
    ];

    // ===== RELATIONS =====
    public function lignes()
    {
        return $this->hasMany(CnpsLigneDeclaration::class, 'declaration_id');
    }

    public function validePar()
    {
        return $this->belongsTo(\App\Models\User::class, 'valide_par');
    }

    // ===== SCOPES =====
public function scopeStatut($query, $statut)
{
    return $query->where('statut', $statut);
}

public function scopePeriode($query, $periode)
{
    return $query->where('periode', $periode);
}

public function scopeADeclarer($query)
{
    return $query->where('statut', 'a_declarer');
}

// ===== MÉTHODE STATIQUE POUR L'APPEL DIRECT =====
public static function aDeclerer()
{
    return self::where('statut', 'a_declarer');
}

    // ===== ACCESSORS =====
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUTS_COLORS[$this->statut] ?? '#f1f5f9';
    }

    public function getStatutTextColorAttribute(): string
    {
        return self::STATUTS_TEXT_COLORS[$this->statut] ?? '#475569';
    }

    public function getMoisLabelAttribute(): string
    {
        return Carbon::createFromFormat('Y-m', $this->periode)->translatedFormat('F Y');
    }

    // ===== MÉTHODES =====
    public static function genererReference(): string
    {
        return 'CNPS-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function peutEtreModifiee(): bool
    {
        return in_array($this->statut, ['a_declarer', 'declare']);
    }

    public function peutEtreSupprimee(): bool
    {
        return $this->statut === 'a_declarer';
    }

    public function calculerTotaux(): array
    {
        $salaireSoumis = $this->lignes->sum('salaire_soumis');
        $cotisationSalariale = $this->lignes->sum('cotisation_salariale');
        $cotisationPatronale = $this->lignes->sum('cotisation_patronale');
        $totalCnps = $cotisationSalariale + $cotisationPatronale;

        return [
            'salaire_soumis' => $salaireSoumis,
            'cotisation_salariale' => $cotisationSalariale,
            'cotisation_patronale' => $cotisationPatronale,
            'total_cnps' => $totalCnps,
        ];
    }

    public function mettreAJourTotaux(): void
    {
        $totaux = $this->calculerTotaux();
        $this->update([
            'total_salaire_soumis' => $totaux['salaire_soumis'],
            'total_cotisation_salariale' => $totaux['cotisation_salariale'],
            'total_cotisation_patronale' => $totaux['cotisation_patronale'],
            'total_cnps' => $totaux['total_cnps'],
        ]);
    }

    /**
     * Taux CNPS au Cameroun (2024)
     * Salarié : 2.52%
     * Employeur : 4.20%
     * Total : 6.72%
     */
    public static function tauxSalarial(): float
    {
        return 0.0252; // 2.52%
    }

    public static function tauxPatronal(): float
    {
        return 0.0420; // 4.20%
    }

    public static function tauxTotal(): float
    {
        return 0.0672; // 6.72%
    }

    public static function calculerCotisationSalariale(float $salaire): float
    {
        return round($salaire * self::tauxSalarial(), 2);
    }

    public static function calculerCotisationPatronale(float $salaire): float
    {
        return round($salaire * self::tauxPatronal(), 2);
    }

    public static function calculerTotalCnps(float $salaire): float
    {
        return round($salaire * self::tauxTotal(), 2);
    }
}