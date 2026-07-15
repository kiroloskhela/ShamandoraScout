<?php

namespace App\Jobs;

use App\Domain\WhatsApp\WhatsAppCampaignService;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Services\WhatsAppBridgeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppCampaignMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // we manage retries ourselves via attempts + chain

    public function __construct(
        public int $campaignId,
        public ?int $recipientId,
    ) {
    }

    public function handle(WhatsAppBridgeClient $bridge, WhatsAppCampaignService $campaigns): void
    {
        $campaign = WhatsAppCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }

        if (in_array($campaign->status, [
            WhatsAppCampaign::STATUS_PAUSED,
            WhatsAppCampaign::STATUS_CANCELLED,
            WhatsAppCampaign::STATUS_COMPLETED,
            WhatsAppCampaign::STATUS_DRAFT,
        ], true)) {
            return;
        }

        // Cap-wait probe without a recipient
        if ($this->recipientId === null) {
            $campaigns->dispatchNext($campaign, 0);

            return;
        }

        /** @var WhatsAppCampaignRecipient|null $recipient */
        $recipient = WhatsAppCampaignRecipient::where('campaign_id', $campaign->id)
            ->where('id', $this->recipientId)
            ->first();

        if (!$recipient) {
            $campaigns->dispatchNext($campaign, 0);

            return;
        }

        if (in_array($recipient->status, [
            WhatsAppCampaignRecipient::STATUS_SENT,
            WhatsAppCampaignRecipient::STATUS_SKIPPED,
            WhatsAppCampaignRecipient::STATUS_CANCELLED,
            WhatsAppCampaignRecipient::STATUS_FAILED,
        ], true)) {
            $campaigns->dispatchNext($campaign, $campaigns->randomDelaySeconds($campaign));

            return;
        }

        if ($campaigns->hourlyCapReached($campaign)) {
            $recipient->update(['status' => WhatsAppCampaignRecipient::STATUS_PENDING]);
            SendWhatsAppCampaignMessage::dispatch($campaign->id, null)
                ->delay(now()->addMinutes(2));

            return;
        }

        $recipient->update([
            'status' => WhatsAppCampaignRecipient::STATUS_SENDING,
            'attempts' => $recipient->attempts + 1,
        ]);

        try {
            $result = $bridge->sendText($recipient->phone, (string) $recipient->personalized_message);
            $recipient->update([
                'status' => WhatsAppCampaignRecipient::STATUS_SENT,
                'whatsapp_message_id' => (string) ($result['messageId'] ?? ''),
                'sent_at' => now(),
                'error_message' => null,
                'error_kind' => null,
            ]);
        } catch (Throwable $e) {
            $kind = $this->classifyError($e);
            $attempts = (int) $recipient->attempts;
            $maxAttempts = 5;

            Log::warning('WhatsApp campaign send failed', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'kind' => $kind,
                'error' => $e->getMessage(),
            ]);

            if ($kind === 'temporary' && $attempts < $maxAttempts) {
                $recipient->update([
                    'status' => WhatsAppCampaignRecipient::STATUS_PENDING,
                    'error_message' => $e->getMessage(),
                    'error_kind' => 'temporary',
                ]);
                $backoff = min(300, (int) (2 ** $attempts) * 5);
                SendWhatsAppCampaignMessage::dispatch($campaign->id, $recipient->id)
                    ->delay(now()->addSeconds($backoff));

                return;
            }

            $recipient->update([
                'status' => WhatsAppCampaignRecipient::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'error_kind' => $kind,
            ]);
        }

        $campaigns->dispatchNext($campaign->fresh(), $campaigns->randomDelaySeconds($campaign));
    }

    private function classifyError(Throwable $e): string
    {
        $msg = strtolower($e->getMessage());

        if (
            str_contains($msg, 'unauthorized')
            || str_contains($msg, '401')
            || str_contains($msg, 'invalid')
            || str_contains($msg, 'not configured')
            || str_contains($msg, '422')
        ) {
            return 'permanent';
        }

        return 'temporary';
    }
}
