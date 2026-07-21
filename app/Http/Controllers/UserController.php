<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // protection Administrateur sur toutes les méthodes
    public function __construct()
    {
        $this->middleware('Administrateuristrateur');
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
            'est_Responsable Direction'   => 'boolean',
            'solde_conge'    => 'nullable|integer',
            'solde_absence'  => 'nullable|integer',
            // On ajoute date_prise_service obligatoire, ne peut pas être dans
            // le futur (une prise de service ne peut pas être "à venir" au
            // moment de la création du compte, sinon l'Agent n'a pas encore
            // commencé). certificat_prise_service : fichier obligatoire,
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
        // (évite les collisions si deux Agents uploadent un fichier du même
        // nom), et retourne le chemin relatif stocké
        $cheminCertificat = Storage::disk('public')->putFile(
            'certificats_prise_service',
            $request->file('certificat_prise_service')
        );

        User::create([
            ...$request->only([
                'matricule', 'nom', 'prenom', 'poste',
                'email', 'password', 'signature',
                'est_responsable_departement',
                'est_Responsable Direction',
                'solde_conge', 'solde_absence',
                'role_id', 'departement_id',
                'date_prise_service',
                'date_prise_service',
            ]),
            'certificat_prise_service' => $cheminCertificat,
        ]);

        $rolesAdditionnels = collect($request->input('roles_additionnels', []))
            ->reject(fn ($id) => (int) $id === (int) $request->role_id);
        $user->rolesAdditionnels()->sync($rolesAdditionnels);

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

    //  pour modifier un utilisateur on a pas besoin de reaploader le certificat 
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
            'est_Responsable Direction'   => 'boolean',
            'solde_conge'    => 'nullable|integer',
            'solde_absence'  => 'nullable|integer',
            'date_prise_service'       => 'required|date|before_or_equal:today',
            // Ajouté "nullable" ici (pas "required" comme au store), et
            // "sometimes" pour ne valider le format que si un fichier est
            // envoyé.
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
            'est_Responsable Direction',
            'solde_conge', 'solde_absence',
            'role_id', 'departement_id',
            'date_prise_service',
        ]);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        // Remplacement du certificat uniquement si un nouveau fichier est
        // envoyé. On supprime l'ancien fichier du disque 

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

        //ajoute la synchronisation des rôles additionnels (multi-roles)
        $rolesAdditionnels = collect($request->input('roles_additionnels', []))
        ->reject(fn ($id) => (int) $id === (int) $request->role_id);
        $user->rolesAdditionnels()->sync($rolesAdditionnels);



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