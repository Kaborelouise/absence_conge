<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Departement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dev = Departement::where('libelle_court', 'DEV')->first();
        $drh = Departement::where('libelle_court', 'DRH')->first();

        $roleAgent    = Role::where('libelle', 'agent')->first();
        $roleChefDept = Role::where('libelle', 'chef_departement')->first();
        $roleRespDir  = Role::where('libelle', 'responsable_direction')->first();
        $roleAgentRh  = Role::where('libelle', 'agent_rh')->first();
        $roleSg       = Role::where('libelle', 'sg')->first();
        $roleDg       = Role::where('libelle', 'dg')->first();
        $rolePca      = Role::where('libelle', 'pca')->first();
        $roleAdmin    = Role::where('libelle', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'issa@gmail.com'],
            [
                'matricule' => 1001, 'nom' => 'Kabore', 'prenom' => 'Issa',
                'poste' => 'Développeur', 'password' => 'issa12',
                'role_id' => $roleAgent->id, 'departement_id' => $dev->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'awa@gmail.com'],
            [
                'matricule' => 1002, 'nom' => 'Ouedraogo', 'prenom' => 'Awa',
                'poste' => 'Chef de département', 'password' => hash::make('awa12'),
                'role_id' => $roleChefDept->id, 'departement_id' => $dev->id,
                'est_responsable_departement' => true, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'etienne@gmail.com'],
            [
                'matricule' => 1003, 'nom' => 'Tougri', 'prenom' => 'Etienne',
                'poste' => 'Responsable de direction', 'password' => Hash::make('etienne12'),
                'role_id' => $roleRespDir->id, 'departement_id' => $dev->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => true,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'traorefatou@gmail.com'],
            [
                'matricule' => 1004, 'nom' => 'Traore', 'prenom' => 'Fatou',
                'poste' => 'Agent RH', 'password' => Hash::make('fatou12'),
                'role_id' => $roleAgentRh->id, 'departement_id' => $drh->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kaborelouise@gmail.com'],
            [
                'matricule' => 1005, 'nom' => 'Kabore', 'prenom' => 'Louise',
                'poste' => 'Secrétaire Général', 'password' => Hash::make('louise12'),
                'role_id' => $roleSg->id, 'departement_id' => $drh->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'zongooctave@gmail.com'],
            [
                'matricule' => 1006, 'nom' => 'Zongo', 'prenom' => 'Octave',
                'poste' => 'Directeur Général', 'password' => Hash::make ('octave12'),
                'role_id' => $roleDg->id, 'departement_id' => $drh->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'dialloamelle'],
            [
                'matricule' => 1007, 'nom' => 'Diallo', 'prenom' => 'Amelle',
                'poste' => 'Président du Conseil d\'Administration', 'password' => Hash::make ('amelle12'),
                'role_id' => $rolePca->id, 'departement_id' => $drh->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@anptic.bf'],
            [
                'matricule' => 1000, 'nom' => 'Admin', 'prenom' => 'Système',
                'poste' => 'Administrateur', 'password' => Hash::make('admin12'),
                'role_id' => $roleAdmin->id, 'departement_id' => $drh->id,
                'est_responsable_departement' => false, 'est_responsable_direction' => false,
                'solde_conge' => 30, 'solde_absence' => 10,
            ]
        );
    }
}