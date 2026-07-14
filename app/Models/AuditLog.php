<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'person_id',
        'actor_name',
        'method',
        'path',
        'route_name',
        'action',
        'ip',
        'user_agent',
        'request_payload',
        'response_status',
        'created_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'created_at' => 'datetime',
    ];
}
