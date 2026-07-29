<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::with('role', 'departement.direction')->get();
    }

    public function headings(): array
    {
        return ['Matricule', 'Nom', 'Prénom', 'Email', 'Rôle', 'Département', 'Direction', 'Dernière connexion'];
    }

    public function map($user): array
    {
        return [
            $user->matricule,
            $user->nom,
            $user->prenom,
            $user->email,
            $user->role->libelle ?? '—',
            $user->departement->libelle_court ?? '—',
            $user->departement->direction->libelle_court ?? '—',
            $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais connecté',
        ];
    }
}