<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Exports\DemandeCongesExport;
use App\Exports\DemandeJouissancesExport;
use App\Exports\DemandeAbsencesExport;
use Maatwebsite\Excel\Facades\Excel;


class AdminExportController extends Controller
{
    public function users()
    {
        return Excel::download(new UsersExport, 'utilisateurs_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function conges()
    {
        return Excel::download(new DemandeCongesExport, 'demandes_conge_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function jouissances()
    {
        return Excel::download(new DemandeJouissancesExport, 'demandes_jouissance_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function absences()
    {
        return Excel::download(new DemandeAbsencesExport, 'demandes_absence_' . now()->format('Y-m-d') . '.xlsx');
    }
}