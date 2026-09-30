<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Departement;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\MotDePasseTemporaireNotification;
use App\Models\Direction;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('Administrateur');
    }

    public function index()
    {
        $users = User::with(['role', 'departement.direction', 'direction'])->get();
        return view('utilisateurs.index', compact('users'));
    }

    public function create()
    {
        $roles        = Role::all();
        $departements = Departement::with('direction')->get();
        $directions   = Direction::orderBy('libelle_court')->get();
        return view('utilisateurs.create', compact('roles', 'departements', 'directions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule'                     => 'required|numeric|unique:users,matricule',
            'nom'                           => 'required|string|max:255',
            'prenom'                        => 'required|string|max:255',
            'poste'                         => 'required|string|max:255',
            'email'                         => 'required|email|unique:users,email',
            'role_id'                       => 'required|exists:roles,id',
            'direction_id'                  => 'nullable|exists:directions,id',
            'departement_id'                => 'nullable|exists:departements,id',
            'date_prise_service'            => 'required|date|before_or_equal:today',
            'certificat_prise_service'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'solde_conge'                   => 'nullable|numeric',
            'solde_absence'                 => 'nullable|numeric',
            'est_responsable_departement'   => 'nullable|boolean',
            'est_responsable_direction'     => 'nullable|boolean',
        ]);

        // Règle de cohérence : si un département est choisi, la direction se déduit de lui.
        // Si aucun département n'est choisi, on garde la direction choisie directement.
        if (!empty($validated['departement_id'])) {
            $departement = Departement::find($validated['departement_id']);

            $validated['direction_id'] = $departement?->direction_id;
        }

      $erreurUnicite = $this->verifierUniciteResponsable(
        roleId: $validated['role_id'],
        departementId: $validated['departement_id'],
        directionId: $validated['direction_id'] ?? null,
        estResponsableDepartement: $request->boolean('est_responsable_departement'),
        estResponsableDirection: $request->boolean('est_responsable_direction'),
    );

        if ($erreurUnicite) {
            return back()->withInput()->with('error', $erreurUnicite);
        }

        $motDePasseTemporaireEnClair = null;

        if ($request->password) {
            // L'admin a choisi de définir lui-même un mot de passe définitif
            $validated['password'] = Hash::make($request->password);
        } else {
            // Génère un mot de passe temporaire lisible, valable 7 jours
            $motDePasseTemporaireEnClair = 'Anp' . now()->year . '-' . Str::random(6);
            $validated['password'] = Hash::make($motDePasseTemporaireEnClair);
            $validated['mot_de_passe_temporaire'] = true;
            $validated['mot_de_passe_expire_at'] = now()->addDays(7);
        }

        if ($request->hasFile('certificat_prise_service')) {
            $validated['certificat_prise_service'] = $request->file('certificat_prise_service')
                ->store('certificats', 'public');
        }

        $user = User::create($validated);

        if ($motDePasseTemporaireEnClair) {
            $user->notify(new MotDePasseTemporaireNotification($motDePasseTemporaireEnClair));

            return redirect()->route('utilisateurs.index')
                ->with('success', "Utilisateur créé. Un email avec son mot de passe temporaire a été envoyé à {$user->email}.");
        }

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show($id)
    {
        $user = User::with(
            'role', 'departement.direction', 'direction',
            'demandeAbsences', 'demandeJouissances'
        )->findOrFail($id);

        return view('utilisateurs.show', compact('user'));
    }

    public function edit($id)
    {
        $user         = User::findOrFail($id);
        $roles        = Role::all();
        $departements = Departement::with('direction')->get();
        $directions   = Direction::orderBy('libelle_court')->get();
        return view('utilisateurs.edit', compact('user', 'roles', 'departements', 'directions'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'matricule'                   => 'required|integer|unique:users,matricule,'.$id,
            'nom'                         => 'required|string|max:255',
            'prenom'                      => 'required|string|max:255',
            'poste'                       => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email,'.$id,
            'role_id'                     => 'required|exists:roles,id',
            'direction_id'                => 'nullable|exists:directions,id',
            'departement_id'              => 'nullable|exists:departements,id',
            'est_responsable_departement' => 'nullable|boolean',
            'est_responsable_direction'   => 'nullable|boolean',
            'solde_conge'                 => 'nullable|integer',
            'solde_absence'               => 'nullable|integer',
            'date_prise_service'          => 'required|date|before_or_equal:today',
            'certificat_prise_service'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'date_prise_service.required'        => 'La date de prise de service est obligatoire.',
            'date_prise_service.before_or_equal' => 'La date de prise de service ne peut pas être dans le futur.',
        ]);

        // si un département est choisi, la direction se déduit de lui.
       if (!empty($validated['departement_id'])) {
            $departement = Departement::find($validated['departement_id']);

            $validated['direction_id'] = $departement?->direction_id;
        }

        $user = User::findOrFail($id);

        $erreurUnicite = $this->verifierUniciteResponsable(
            roleId: $validated['role_id'],
            departementId: $validated['departement_id'],
            directionId: $validated['direction_id'],
            estResponsableDepartement: $request->boolean('est_responsable_departement'),
            estResponsableDirection: $request->boolean('est_responsable_direction'),
            ignorerUserId: $user->id,
        );

        if ($erreurUnicite) {
            return back()->withInput()->with('error', $erreurUnicite);
        }

        $data = [
            'matricule'                   => $validated['matricule'],
            'nom'                         => strtoupper($validated['nom']),
            'prenom'                      => $validated['prenom'],
            'poste'                       => $validated['poste'],
            'email'                       => $validated['email'],
            'est_responsable_departement' => $request->boolean('est_responsable_departement'),
            'est_responsable_direction'   => $request->boolean('est_responsable_direction'),
            'solde_conge'                 => $validated['solde_conge'],
            'solde_absence'               => $validated['solde_absence'],
            'role_id'                     => $validated['role_id'],
            'departement_id'              => $validated['departement_id'],
            'direction_id'                => $validated['direction_id'],
            'date_prise_service'          => $validated['date_prise_service'],
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

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

        LogActivity::log(
            'update', 'User', $user->id,
            "Modification utilisateur {$user->nom} {$user->prenom}"
        );

        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur modifié avec succès.');
}


        public function destroy($id)
        {
            $user = User::findOrFail($id);

            // Empêche la suppression si l'utilisateur a des demandes enregistrées
            // (traçabilité RH + évite la violation de contrainte de clé étrangère)
            $aDesDemandes = $user->demandeAbsences()->exists()
                || $user->demandeJouissances()->exists()
                || (method_exists($user, 'demandeConges') && $user->demandeConges()->exists());

            if ($aDesDemandes) {
                return redirect()->route('utilisateurs.index')
                    ->with('error', "Impossible de supprimer {$user->prenom} {$user->nom} : cet utilisateur a des demandes enregistrées dans le système. Envisagez de désactiver son compte plutôt que de le supprimer.");
            }

            try {
                if ($user->certificat_prise_service) {
                    Storage::disk('public')->delete($user->certificat_prise_service);
                }

                $nomComplet = "{$user->nom} {$user->prenom}";
                $user->delete();

                LogActivity::log('delete', 'User', $id, "Suppression utilisateur {$nomComplet}");

                return redirect()
                    ->route('utilisateurs.index')
                    ->with('success', 'Utilisateur supprimé.');

            } catch (\Illuminate\Database\QueryException $e) {
                
                return redirect()->route('utilisateurs.index')
                    ->with('error', "Impossible de supprimer {$user->prenom} {$user->nom} : des données liées existent encore dans le système.");
            }
        }

        public function renvoyerInvitation(User $utilisateur)
        {
            $motDePasseTemporaireEnClair = 'Anp' . now()->year . '-' . Str::random(6);

            $utilisateur->update([
                'password'                => Hash::make($motDePasseTemporaireEnClair),
                'mot_de_passe_temporaire' => true,
                'mot_de_passe_expire_at'  => now()->addDays(7),
            ]);

            $utilisateur->notify(new MotDePasseTemporaireNotification($motDePasseTemporaireEnClair));

            return back()->with('success', "Un nouveau mot de passe temporaire a été envoyé à {$utilisateur->email}.");
        }


        private function verifierUniciteResponsable(
            int $roleId,
            ?int $departementId,
            ?int $directionId,
            bool $estResponsableDepartement,
            bool $estResponsableDirection,
            ?int $ignorerUserId = null
        ): ?string {
            $role = Role::find($roleId);
            $departement = $departementId ? Departement::find($departementId) : null;

            // Un utilisateur marqué "Chef de département" doit obligatoirement avoir un département
            $estChefDepartement = $estResponsableDepartement || ($role && $role->libelle === 'Chef de Département');

            if ($estChefDepartement && !$departementId) {
                return "Un Chef de département doit obligatoirement être rattaché à un département.";
            }

            if ($estChefDepartement && $departementId) {
                $existeDeja = User::where('departement_id', $departementId)
                    ->where(function ($q) {
                        $q->where('est_responsable_departement', true)
                        ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Chef de Département'));
                    })
                    ->when($ignorerUserId, fn ($q) => $q->where('id', '!=', $ignorerUserId))
                    ->exists();

                if ($existeDeja) {
                    return "Le département « {$departement?->libelle_court} » a déjà un Chef de Département. Un seul chef par département est autorisé.";
                }
            }

            // Un seul Responsable de Direction par direction — la direction se déduit
            // soit du département choisi, soit du rattachement direct
            $estRespDirection = $estResponsableDirection || ($role && $role->libelle === 'Responsable Direction');

            if ($estRespDirection) {
                $directionReelleId = $departement?->direction_id ?? $directionId;

                if (!$directionReelleId) {
                    return "Un Responsable de direction doit obligatoirement être rattaché à une direction.";
                }

                $existeDeja = User::where(function ($q) use ($directionReelleId) {
                        $q->where('direction_id', $directionReelleId)
                        ->orWhereHas('departement', fn ($q2) => $q2->where('direction_id', $directionReelleId));
                    })
                    ->where(function ($q) {
                        $q->where('est_responsable_direction', true)
                        ->orWhereHas('role', fn ($q2) => $q2->where('libelle', 'Responsable Direction'));
                    })
                    ->when($ignorerUserId, fn ($q) => $q->where('id', '!=', $ignorerUserId))
                    ->exists();

                if ($existeDeja) {
                    $nomDirection = $departement?->direction?->libelle_court ?? Direction::find($directionId)?->libelle_court;
                    return "La direction « {$nomDirection} » a déjà un Responsable de Direction. Un seul responsable par direction est autorisé.";
                }
            }

            return null;
        }
}