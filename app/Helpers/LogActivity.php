<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class LogActivity
{
    // Enregistre une action dans les logs
    public static function log(
        string $action,
        string $model,
        ?int $modelId = null,
        ?string $description = null
    ): void {
        // Ne log que si un utilisateur est connecté
        if (!auth()->check()) return;

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'model'       => $model,
            'model_id'    => $modelId,
            'description' => $description,
        ]);
    }
}