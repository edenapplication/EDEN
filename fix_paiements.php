<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BonPaiement;
use App\Models\PaiementDossier;
use App\Models\PaiementTechnique;
use App\Models\PaiementLogistique;
use App\Models\PaiementMorcellement;

$MODE = ($argv[1] ?? '') === 'executer';
echo "Mode : " . ($MODE ? "EXÉCUTION" : "SIMULATION") . "\n\n";

$stats = ['tech'=>0, 'dossier'=>0, 'logi'=>0, 'morc'=>0];

foreach (BonPaiement::all() as $bon) {
    $did = $bon->dossier_client_id;
    $ref = $bon->user_reference ?? 'RECONSTRUIT';
    $date = $bon->date_bon;

    if ($bon->versement_technique > 0 && !PaiementTechnique::where('dossier_client_id',$did)->where('montant',$bon->versement_technique)->exists()) {
        $stats['tech']++;
        if ($MODE) PaiementTechnique::create(['dossier_client_id'=>$did,'montant'=>$bon->versement_technique,'date_paiement'=>$date,'note'=>'Bon '.$bon->numero_bon,'user_reference'=>$ref]);
    }
    if ($bon->versement_dossier > 0 && !PaiementDossier::where('dossier_client_id',$did)->where('montant',$bon->versement_dossier)->exists()) {
        $stats['dossier']++;
        if ($MODE) PaiementDossier::create(['dossier_client_id'=>$did,'client_id'=>$bon->dossier?->client_id,'montant'=>$bon->versement_dossier,'reste'=>0,'date_paiement'=>$date,'note'=>'Bon '.$bon->numero_bon,'user_reference'=>$ref]);
    }
    if ($bon->versement_logistique > 0 && !PaiementLogistique::where('dossier_client_id',$did)->where('montant',$bon->versement_logistique)->exists()) {
        $stats['logi']++;
        if ($MODE) PaiementLogistique::create(['dossier_client_id'=>$did,'montant'=>$bon->versement_logistique,'date_paiement'=>$date,'note'=>'Bon '.$bon->numero_bon,'user_reference'=>$ref]);
    }
    if ($bon->versement_morcellement > 0 && !PaiementMorcellement::where('dossier_client_id',$did)->where('montant',$bon->versement_morcellement)->exists()) {
        $stats['morc']++;
        if ($MODE) PaiementMorcellement::create(['dossier_client_id'=>$did,'montant'=>$bon->versement_morcellement,'date_paiement'=>$date,'note'=>'Bon '.$bon->numero_bon,'user_reference'=>$ref]);
    }
}

echo "Tech    : {$stats['tech']}\n";
echo "Dossier : {$stats['dossier']}\n";
echo "Logi    : {$stats['logi']}\n";
echo "Morc    : {$stats['morc']}\n";
echo "TOTAL   : " . array_sum($stats) . "\n";