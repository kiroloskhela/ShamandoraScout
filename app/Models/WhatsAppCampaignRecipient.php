<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppCampaignRecipient extends Model
{
    protected $table = 'whatsapp_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'person_id',
        'phone',
        'personalized_message',
        'status',
        'attempts',
        'whatsapp_message_id',
        'error_message',
        'error_kind',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_CANCELLED = 'cancelled';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(User::class, 'person_id', 'PersonID');
    }
}
