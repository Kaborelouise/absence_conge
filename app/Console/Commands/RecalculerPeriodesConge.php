<?php

namespace App\Console\Commands;

use App\Models\DemandeConge;
use Illuminate\Console\Command;

class RecalculerPeriodesConge extends Command
{
    protected $signature = 'conges:recalculer {--dry-run : Afficher sans enregistrer}';
    protected $description = 'Recalcule date_debut, date_fin et date_effet des demandes de congé non compilées.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $demandes = DemandeConge::with('user')
            ->where('statut', '!=', 'compilee')
            ->get();

        if ($demandes->isEmpty()) {
            $this->info('Aucune demande non compilée à recalculer.');
            return;
        }

        $this->table(
            ['ID', 'Agent', 'Ancien début', 'Ancien effet', '→', 'Nouveau début', 'Nouvel effet'],
            $demandes->map(function ($demande) use ($dryRun) {
                $user = $demande->user;

                if (!$user || !$user->date_prise_service) {
                    return [$demande->id, $user->nom ?? '?', $demande->date_debut, $demande->date_effet, '⚠', 'SKIP', 'date_prise_service manquante'];
                }

                $periode = $user->prochainePeriodeConge();
                $ancienDebut = $demande->date_debut;
                $ancienEffet = $demande->date_effet;

                if (!$dryRun) {
                    $demande->update([
                        'date_debut' => $periode['debut_travail']->format('Y-m-d'),
                        'date_fin'   => $periode['fin_travail']->format('Y-m-d'),
                        'date_effet' => $periode['date_effet']->format('Y-m-d'),
                    ]);
                }

                return [
                    $demande->id,
                    $user->nom . ' ' . $user->prenom,
                    $ancienDebut,
                    $ancienEffet,
                    '→',
                    $periode['debut_travail']->format('d/m/Y'),
                    $periode['date_effet']->format('d/m/Y'),
                ];
            })
        );

        if ($dryRun) {
            $this->warn('Mode --dry-run : rien n\'a été enregistré.');
        } else {
            $this->info($demandes->count() . ' demande(s) recalculée(s) et enregistrée(s).');
        }
    }
}