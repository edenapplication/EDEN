<?php

namespace App\Helpers;

class NombreHelper
{
    public static function enLettres($nombre)
    {
        $unites = [
            "", "un", "deux", "trois", "quatre", "cinq",
            "six", "sept", "huit", "neuf", "dix",
            "onze", "douze", "treize", "quatorze",
            "quinze", "seize", "dix-sept",
            "dix-huit", "dix-neuf"
        ];

        $nombre = intval($nombre);

        if ($nombre == 0) {
            return "zéro Franc CFA";
        }

        $texte = self::convertir($nombre);

        return ucfirst(trim($texte)) . " Franc CFA";
    }


    private static function convertir($nombre)
    {
        $unites = [
            "", "un", "deux", "trois", "quatre",
            "cinq", "six", "sept", "huit", "neuf",
            "dix", "onze", "douze", "treize",
            "quatorze", "quinze", "seize",
            "dix-sept", "dix-huit", "dix-neuf"
        ];

        if ($nombre < 20) {
            return $unites[$nombre];
        }


        if ($nombre < 100) {

            $dizaines = [
                20 => "vingt",
                30 => "trente",
                40 => "quarante",
                50 => "cinquante",
                60 => "soixante",
                70 => "soixante-dix",
                80 => "quatre-vingt",
                90 => "quatre-vingt-dix"
            ];

            if ($nombre % 10 == 0) {
                return $dizaines[$nombre];
            }

            if ($nombre < 70) {
                return $dizaines[$nombre - ($nombre % 10)]
                    . "-" . $unites[$nombre % 10];
            }

            return self::convertir($nombre - 10)
                ;
        }


        if ($nombre < 1000) {

            $cent = intdiv($nombre,100);
            $reste = $nombre % 100;

            $texte = ($cent == 1)
                ? "cent"
                : $unites[$cent] . " cent";

            if ($reste > 0) {
                $texte .= " " . self::convertir($reste);
            }

            return $texte;
        }


        if ($nombre < 1000000) {

            $mille = intdiv($nombre,1000);
            $reste = $nombre % 1000;

            $texte = ($mille == 1)
                ? "mille"
                : self::convertir($mille)." mille";

            if ($reste > 0) {
                $texte .= " ".self::convertir($reste);
            }

            return $texte;
        }


        if ($nombre < 1000000000) {

            $million = intdiv($nombre,1000000);
            $reste = $nombre % 1000000;

            $texte = ($million == 1)
                ? "un million"
                : self::convertir($million)." millions";

            if ($reste > 0) {
                $texte .= " ".self::convertir($reste);
            }

            return $texte;
        }

        return "";
    }
}