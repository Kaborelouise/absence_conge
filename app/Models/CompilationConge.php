<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompilationConge extends Model
{
    protected $table = 'compilations_conges';

    protected $fillable = [
        'annee',
        'session_Administrateuristrative_id',
        'compiled_by',
        'compiled_at',
        'decompilee_at',
    ];

    protected $casts = [
        'compiled_at'   => 'datetime',
        'decompilee_at' => 'datetime',
    ];

    public function sessionAdministrateuristrative()
    {
        return $this->belongsTo(SessionAdministrateuristrative::class, 'session_Administrateuristrative_id');
    }

    public function compilePar()
    {
        return $this->belongsTo(User::class, 'compiled_by');
    }

    /**
     * Retourne la compilation active (compilée et pas encore décompilée) pour
     * la session donnée, ou null s'il n'y en a pas.
     */
    public static function activeParSession(int $sessionId): ?self
    {
        return self::where('session_Administrateuristrative_id', $sessionId)
            ->whereNotNull('compiled_at')
            ->whereNull('decompilee_at')
            ->first();
    }

    /**
     * La compilation est-elle actuellement active (compilée et pas encore
     * décompilée) ?
     */
    public function estActive(): bool
    {
        return $this->compiled_at !== null && $this->decompilee_at === null;
    }
}