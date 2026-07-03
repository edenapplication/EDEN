<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feb\Colonne;

class FebColonnesSeeder extends Seeder
{
    public function run(): void
    {
        $colonnes = [
            ['libelle' => 'Désignation',  'description' => 'Description de l\'article ou du service', 'ordre' => 1],
            ['libelle' => 'Quantité',     'description' => 'Nombre d\'unités',                        'ordre' => 2],
            ['libelle' => 'Prix Unitaire','description' => 'Prix par unité en FCFA',                  'ordre' => 3],
            ['libelle' => 'Prix Total',   'description' => 'Calculé automatiquement (Qté × PU)',       'ordre' => 4],
            ['libelle' => 'Fournisseur',  'description' => 'Nom du fournisseur proposé',              'ordre' => 5],
            ['libelle' => 'Référence',    'description' => 'Référence article ou code',               'ordre' => 6],
            ['libelle' => 'Observations', 'description' => 'Remarques ou commentaires',               'ordre' => 7],
        ];

        foreach ($colonnes as $c) {
            Colonne::firstOrCreate(
                ['libelle' => $c['libelle']],
                array_merge($c, ['actif' => true])
            );
        }
    }
}