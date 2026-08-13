<?php

// app/Models/RefreshToken.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id', 'token_hash', 'family_id', 'expires_at', 'revoked_at', 'replaced_by_id', 'ip', 'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        // local key on this table = user_id
        // owner key on PersonInformation = PersonID
        return $this->belongsTo(User::class, 'user_id', 'PersonID');
    }

    public function scopeActive($q)
    {
        return $q->whereNull('revoked_at')->where('expires_at', '>', now());
    }
}