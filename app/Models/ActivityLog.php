<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'description',
    ];

    // Relation vers l'utilisateur qui a fait l'action
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}