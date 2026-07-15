<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeConge extends Model
{
        protected $fillable = [
        // AJOUTÉ : absent jusqu'ici du fillable, c'était la cause du "N° Demande"
        // vide (voir migration add_num_demande_to_demande_conges_table) :
        // Laravel ignorait silencieusement ce champ en mass-assignment.
        'num_demande',
        'lieu_jouissance',
        'user_id',
        'abandonnee',
        // AJOUTÉ : rattachement à la campagne annuelle.
        'session_administrative_id',
        // AJOUTÉ (bug critique repéré à la relecture) : sans ce champ dans le
        // fillable, DemandeCongeController::compiler()/decompiler() faisaient
        // $demande->update(['statut' => 'compilee']) qui échouait
        // SILENCIEUSEMENT (aucune erreur visible, mais le champ n'était
        // jamais réellement modifié en base). estCompilee() se base sur
        // avisConge (pas sur 'statut'), donc le bug ne se serait vu qu'à
        // l'usage du champ statut ailleurs (ex: dans l'affichage du badge).
        'statut',
                 ];

    protected $casts = [
        'lieu_jouissance' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * NOUVEAU : session administrative (campagne annuelle) de cette demande.
     * Pour DemandeConge, cette relation est STRUCTURANTE (pas juste
     * informative) : la création est bloquée si aucune session n'est active
     * pour le congé (voir DemandeCongeController::store()).
     */
    public function sessionAdministrative()
    {
        return $this->belongsTo(SessionAdministrative::class, 'session_administrative_id');
    }

    // Une demande de congé peut avoir 0 ou 1 avis (compilation RH)
    public function avisConge()
    {
        return $this->hasOne(AvisConge::class);
    }

    public function estCompilee(): bool
    {
        return $this->avisConge !== null;
    }
    public function peutEtreCompileePar(User $user): bool
    {
        if ($this->estCompilee()) {
            return false;
        }

        return $user->role->libelle === 'agent_rh';
    }

    //un agent peut abandonner sa demande si elle n'est pas encore compilée
    public function peutEtreAbandonneePar(User $user): bool

    {
        if ($this->abandonnee || $this->estCompilee())
            {
                return false;
            }

            return $this->user_id === $user->id;
    }
}