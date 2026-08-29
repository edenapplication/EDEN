<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class CnpsLigneDeclaration extends Model
{
    protected $table = 'rh_cnps_lignes_declaration';

    protected $fillable = [
        'declaration_id',
        'employe_id',
        'bulletin_paie_id',
        'numero_cnps',
        'nom',
        'prenom',
        'matricule',
        'salaire_soumis',
        'cotisation_salariale',
        'cotisation_patronale',
        'total_cnps',
        'observations',
    ];

    protected $casts = [
        'salaire_soumis' => 'decimal:2',
        'cotisation_salariale' => 'decimal:2',
        'cotisation_patronale' => 'decimal:2',
        'total_cnps' => 'decimal:2',
    ];

    // ===== RELATIONS =====
    public function declaration()
    {
        return $this->belongsTo(CnpsDeclaration::class, 'declaration_id');
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function bulletinPaie()
    {
        return $this->belongsTo(BulletinPaie::class, 'bulletin_paie_id');
    }

    // ===== SCOPES =====
    public function scopeDeclaration($query, $declarationId)
    {
        return $query->where('declaration_id', $declarationId);
    }

    // ===== MÉTHODES =====
    public static function calculerLigne(float $salaire): array
    {
        $cotisationSalariale = CnpsDeclaration::calculerCotisationSalariale($salaire);
        $cotisationPatronale = CnpsDeclaration::calculerCotisationPatronale($salaire);

        return [
            'cotisation_salariale' => $cotisationSalariale,
            'cotisation_patronale' => $cotisationPatronale,
            'total_cnps' => $cotisationSalariale + $cotisationPatronale,
        ];
    }
}