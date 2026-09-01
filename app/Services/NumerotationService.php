<?php

namespace App\Services;

use App\Models\CompteurNumerotation;
use App\Models\ParametreDocument;

class NumerotationService
{
    public static function genererNumero(string $type, ?int $annee = null): string
    {
        $annee = $annee ?? now()->year;

        $compteur = CompteurNumerotation::firstOrCreate(
            ['type' => $type, 'annee' => $annee],
            ['dernier_numero' => 0]
        );

        $compteur->increment('dernier_numero');

        $parametres = ParametreDocument::actuel();

        $nbChiffres = match ($type) {
            'decision'           => $parametres->nb_chiffres_decision,
            'cessation_service'  => $parametres->nb_chiffres_cessation,
            'prise_service'      => $parametres->nb_chiffres_prise_service,
            'interim'            => $parametres->nb_chiffres_interim,
            default              => 5,
        };

        $numeroFormate = str_pad($compteur->dernier_numero, $nbChiffres, '0', STR_PAD_LEFT);

        return "{$annee}-{$numeroFormate}/{$parametres->sigle_ministere}/{$parametres->sigle_secretariat}/{$parametres->sigle_agence}/DRH";
    }

    public static function reinitialiserCompteurs(int $annee): void
    {
        CompteurNumerotation::where('annee', $annee)->delete();
    }
}