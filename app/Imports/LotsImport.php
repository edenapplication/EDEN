<?php

namespace App\Imports;

use App\Models\Bloc;
use App\Models\GrandSite;
use App\Models\LotAffectation;
use App\Models\Site;
use App\Models\Tf;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class LotsImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    public $imported      = 0;
    public $blocsCreated  = 0;
    public $skipped       = 0;
    public $errors        = [];

    protected $cacheGrandSites = [];
    protected $cacheSites      = [];
    protected $cacheTfs        = [];
    protected $cacheBlocs      = [];

    public function model(array $row)
    {
        $grandSiteNom = trim($row['grand_site'] ?? '');
        $siteNom      = trim($row['site'] ?? '');
        $tfRef        = trim($row['tf'] ?? '');
        $blocCode     = strtoupper(trim($row['bloc'] ?? ''));
        $numerosRaw   = trim($row['numeros'] ?? '');
        $superficie   = $row['superficie'] ?? null;

        if (empty($grandSiteNom) || empty($blocCode) || empty($numerosRaw)) {
            $this->errors[] = "Ligne ignorée : grand_site, bloc et numeros sont obligatoires.";
            $this->skipped++;
            return null;
        }

        // Grand Site
        if (!isset($this->cacheGrandSites[$grandSiteNom])) {
            $gs = GrandSite::where('nom', $grandSiteNom)->first();
            if (!$gs) {
                $this->errors[] = "Grand Site '{$grandSiteNom}' introuvable. Bloc '{$blocCode}' ignoré.";
                $this->skipped++;
                return null;
            }
            $this->cacheGrandSites[$grandSiteNom] = $gs;
        }
        $grandSite = $this->cacheGrandSites[$grandSiteNom];

        // Site (optionnel)
        $site = null;
        if (!empty($siteNom)) {
            $key = $grandSite->id . '|' . $siteNom;
            if (!isset($this->cacheSites[$key])) {
                $this->cacheSites[$key] = Site::where('grand_site_id', $grandSite->id)
                                              ->where('name', $siteNom)
                                              ->first();
            }
            $site = $this->cacheSites[$key];
        }

        // TF (optionnel)
        $tf = null;
        if (!empty($tfRef)) {
            if (!isset($this->cacheTfs[$tfRef])) {
                $this->cacheTfs[$tfRef] = Tf::where('title', $tfRef)->first();
            }
            $tf = $this->cacheTfs[$tfRef];
        }

        // Bloc — créer si inexistant
        $blocKey = ($tf->id ?? 0) . '|' . $blocCode;
        if (!isset($this->cacheBlocs[$blocKey])) {
            $bloc = Bloc::where('tf_id', $tf->id ?? null)
                        ->where('code', $blocCode)
                        ->first();

            if (!$bloc) {
                $bloc = Bloc::create([
                    'grand_site_id' => $grandSite->id,
                    'site_id'       => $site->id ?? null,
                    'tf_id'         => $tf->id ?? null,
                    'code'          => $blocCode,
                    'description'   => 'Créé automatiquement par import Excel',
                    'actif'         => true,
                ]);
                $this->blocsCreated++;
            }
            $this->cacheBlocs[$blocKey] = $bloc;
        }
        $bloc = $this->cacheBlocs[$blocKey];

        // Lots séparés par ;
        $numeros = array_filter(array_map('trim', explode(';', $numerosRaw)));

        if (empty($numeros)) {
            $this->errors[] = "Bloc '{$blocCode}' : aucun numéro de lot valide.";
            $this->skipped++;
            return null;
        }

        $nbCrees   = 0;
        $nbIgnores = 0;

        foreach ($numeros as $numero) {
            if (empty($numero)) continue;

            $exists = LotAffectation::where('bloc_id', $bloc->id)
                                    ->where('numero', $numero)
                                    ->exists();
            if ($exists) { $nbIgnores++; continue; }

            LotAffectation::create([
                'grand_site_id' => $grandSite->id,
                'site_id'       => $site->id ?? null,
                'tf_id'         => $tf->id ?? null,
                'bloc_id'       => $bloc->id,
                'numero'        => $numero,
                'superficie'    => !empty($superficie) ? floatval($superficie) : null,
                'disponible'    => true,
                'actif'         => true,
            ]);

            $nbCrees++;
            $this->imported++;
        }

        if ($nbCrees === 0 && $nbIgnores > 0) {
            $this->skipped++;
            $this->errors[] = "Bloc '{$blocCode}' : tous les lots existent déjà ({$nbIgnores}).";
        }

        return null;
    }

    public function onError(Throwable $e) { $this->errors[] = $e->getMessage(); }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Ligne {$failure->row()} : " . implode(', ', $failure->errors());
        }
    }
}