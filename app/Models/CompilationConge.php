<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompilationConge extends Model
{
    protected $table = 'compilations_conges';

    protected $fillable = [
        'annee',
        'session_administrative_id',
        'compiled_by',
        'compiled_at',
        'decompilee_at',
    ];

    protected $casts = [
        'compiled_at'   => 'datetime',
        'decompilee_at' => 'datetime',
    ];

    public function sessionAdministrative()
    {
        return $this->belongsTo(SessionAdministrative::class, 'session_administrative_id');
    }

    public function compilePar()
    {
        return $this->belongsTo(User::class, 'compiled_by');
    }

    public static function activeParSession(int $sessionId): ?self
    {
        return self::where('session_administrative_id', $sessionId)
            ->whereNotNull('compiled_at')
            ->whereNull('decompilee_at')
            ->first();
    }

    public function estActive(): bool
    {
        return $this->compiled_at !== null
            && $this->decompilee_at === null;
    }
}