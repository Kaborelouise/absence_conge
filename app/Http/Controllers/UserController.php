<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // protection admin sur toutes les méthodes
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $users = User::with('role', 'departement.direction')->get();
        return view('utilisateurs.index', compact('users'));
    }

    public function create()
    {
        $roles        = Role::all();
        $departements = Departement::with('direction')->get();
        return view('utilisateurs.create', compact('roles', 'departements'));
    }

    /**
     * MODIFIÉ : ajout de la gestion des deux nouveaux champs du cycle
     * congé/jouissance :
     * - date_prise_service : obligatoire à la création (contrairement à la
     *   colonne en base qui est nullable, pour ne pas casser les agents déjà
     *   existants — voir la migration). Sans cette date, on ne peut pas
     *   calculer l'éligibilité au congé de l'agent (User::estEligibleAuConge()).
     * - certificat_prise_service ou arrêté d'intégration : fichier justificatif (PDF/image), stocké
     *   sur le disque "public" dans le dossier certificats_prise_service. On
     *   enregistre uniquement le chemin du fichier en base (pas le fichier
     *   lui-même), c'est Storage qui gère l'écriture physique sur disque.
     */
    public function store(Request $request)
    {
        $request->validate([
            'matricule'      => 'required|integer|unique:users,matricule',
            'nom'            => 'required|string|max:255',
            'prenom'         => 'required|string|max:255',
            'poste'          => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
            'role_id'        => 'required|exists:roles,id',
            'departement_id' => 'required|exists:departements,id',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction'   => 'boolean',
            'solde_conge'    => 'nullable|integer',
            'solde_absence'  => 'nullable|integer',
            // Ajout date_prise_service obligatoire, ne peut pas être dans
            // le futur (une prise de service ne peut pas être "à venir" au
            // moment de la création du compte, sinon l'agent n'a pas encore
            // commencé). certificat_prise_service : fichier obligatoire,
            // formats acceptés PDF/JPG/PNG, 5 Mo max.
            'date_prise_service'       => 'required|date|before_or_equal:today',
            'certificat_prise_service' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            // Messages d'erreur en français
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
            'matricule.unique'  => 'Ce matricule est déjà utilisé.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'date_prise_service.required'      => 'La date de prise de service est obligatoire.',
            'date_prise_service.before_or_equal' => 'La date de prise de service ne peut pas être dans le futur.',
            'certificat_prise_service.required' => 'Le certificat/arrêté de prise de service est obligatoire.',
            'certificat_prise_service.mimes'    => 'Le certificat doit être au format PDF, JPG ou PNG.',
        ]);

        // Upload du certificat avant la création de l'utilisateur, pour
        // récupérer le chemin du fichier stocké et l'inclure dans le create().
        // Storage::putFile() génère un nom de fichier unique automatiquement
        // (évite les collisions si deux agents uploadent un fichier du même
        // nom), et retourne le chemin relatif stocké.
        $cheminCertificat = Storage::disk('public')->putFile(
            'certificats_prise_service',
            $request->file('certificat_prise_service')
        );

        User::create([
            ...$request->only([
                'matricule', 'nom', 'prenom', 'poste',
                'email', 'password', 'signature',
                'est_responsable_departement',
                'est_responsable_direction',
                'solde_conge', 'solde_absence',
                'role_id', 'departement_id',
                'date_prise_service',
            ]),
            'certificat_prise_service' => $cheminCertificat,
        ]);

        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    public function edit($id)
    {
        $user         = User::findOrFail($id);
        $roles        = Role::all();
        $departements = Departement::with('direction')->get();
        return view('utilisateurs.edit', compact('user', 'roles', 'departements'));
    }

    /**
     * MODIFIÉ : le certificat n'est PAS obligatoire à la modification (un
     * admin qui corrige juste le nom de l'agent ne doit pas être forcé de
     * re-uploader le certificat à chaque fois). S'il uploade un nouveau
     * fichier, on remplace l'ancien ; sinon on garde le chemin existant.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'matricule'      => 'required|integer|unique:users,matricule,'.$id,
            'nom'            => 'required|string|max:255',
            'prenom'         => 'required|string|max:255',
            'poste'          => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,'.$id,
            'password'       => 'nullable|string|min:8',
            'role_id'        => 'required|exists:roles,id',
            'departement_id' => 'required|exists:departements,id',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction'   => 'boolean',
            'solde_conge'    => 'nullable|integer',
            'solde_absence'  => 'nullable|integer',
            'date_prise_service'       => 'required|date|before_or_equal:today',
            // Ajouté : "nullable" ici (pas "required" comme au store), et
            // "sometimes" pour ne valider le format que si un fichier est
            // effectivement envoyé.
            'certificat_prise_service' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'date_prise_service.required'      => 'La date de prise de service est obligatoire.',
            'date_prise_service.before_or_equal' => 'La date de prise de service ne peut pas être dans le futur.',
            'certificat_prise_service.mimes'    => 'Le certificat doit être au format PDF, JPG ou PNG.',
        ]);

        $user = User::findOrFail($id);

        $data = $request->only([
            'matricule', 'nom', 'prenom', 'poste',
            'email', 'signature',
            'est_responsable_departement',
            'est_responsable_direction',
            'solde_conge', 'solde_absence',
            'role_id', 'departement_id',
            'date_prise_service',
        ]);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        // Remplacement  du certificat uniquement si un nouveau fichier est
        // envoyé. On supprime l'ancien fichier du disque pour ne pas laisser
        // de fichiers orphelins s'accumuler dans le stockage.
        if ($request->hasFile('certificat_prise_service')) {
            if ($user->certificat_prise_service) {
                Storage::disk('public')->delete($user->certificat_prise_service);
            }

            $data['certificat_prise_service'] = Storage::disk('public')->putFile(
                'certificats_prise_service',
                $request->file('certificat_prise_service')
            );
        }

        $user->update($data);

        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur modifié avec succès');
    }


    public function show($id)
    {
        $user = User::with('role', 'departement.direction', 
                        'demandeAbsences', 'demandeJouissances')
                    ->findOrFail($id);
        return view('utilisateurs.show', compact('user'));
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé');
    }
}