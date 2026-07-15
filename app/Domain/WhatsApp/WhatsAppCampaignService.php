<?php

namespace App\Domain\WhatsApp;

use App\Jobs\SendWhatsAppCampaignMessage;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Services\WhatsAppBridgeClient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WhatsAppCampaignService
{
    public const HIGH_COUNT_THRESHOLD = 100;

    public function __construct(
        private readonly MessagePersonalizer $personalizer,
        private readonly CampaignRecipientQuery $query,
        private readonly WhatsAppBridgeClient $bridge,
    ) {
    }

    /**
     * @param  array{
     *   name: string,
     *   message_template: string,
     *   missing_variable_behavior?: string,
     *   fallback_name?: ?string,
     *   min_delay_seconds?: int,
     *   max_delay_seconds?: int,
     *   max_messages_per_hour?: int,
     *   person_ids?: list<int>,
     *   select_all_filters?: array<string, mixed>|null
     * }  $data
     */
    public function createDraft(array $data, int $createdBy): WhatsAppCampaign
    {
        $minDelay = max(1, (int) ($data['min_delay_seconds'] ?? 8));
        $maxDelay = max($minDelay, (int) ($data['max_delay_seconds'] ?? 15));

        $campaign = WhatsAppCampaign::create([
            'name' => trim($data['name']),
            'message_template' => (string) $data['message_template'],
            'status' => WhatsAppCampaign::STATUS_DRAFT,
            'missing_variable_behavior' => $data['missing_variable_behavior'] ?? 'fallback',
            'fallback_name' => $data['fallback_name'] ?? 'صديقنا',
            'min_delay_seconds' => $minDelay,
            'max_delay_seconds' => $maxDelay,
            'max_messages_per_hour' => max(1, (int) ($data['max_messages_per_hour'] ?? 60)),
            'created_by' => $createdBy,
        ]);

        $this->syncRecipients($campaign, $data);

        return $campaign->fresh(['recipients']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(WhatsAppCampaign $campaign, array $data): WhatsAppCampaign
    {
        if (!$campaign->isEditable()) {
            throw new RuntimeException('Only draft campaigns can be edited.');
        }

        $minDelay = max(1, (int) ($data['min_delay_seconds'] ?? $campaign->min_delay_seconds));
        $maxDelay = max($minDelay, (int) ($data['max_delay_seconds'] ?? $campaign->max_delay_seconds));

        $campaign->update([
            'name' => trim((string) ($data['name'] ?? $campaign->name)),
            'message_template' => (string) ($data['message_template'] ?? $campaign->message_template),
            'missing_variable_behavior' => $data['missing_variable_behavior'] ?? $campaign->missing_variable_behavior,
            'fallback_name' => $data['fallback_name'] ?? $campaign->fallback_name,
            'min_delay_seconds' => $minDelay,
            'max_delay_seconds' => $maxDelay,
            'max_messages_per_hour' => max(1, (int) ($data['max_messages_per_hour'] ?? $campaign->max_messages_per_hour)),
        ]);

        if (array_key_exists('person_ids', $data) || array_key_exists('select_all_filters', $data)) {
            $this->syncRecipients($campaign->fresh(), $data);
        }

        return $campaign->fresh(['recipients']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRecipients(WhatsAppCampaign $campaign, array $data): void
    {
        $people = collect();

        if (!empty($data['select_all_filters']) && is_array($data['select_all_filters'])) {
            $people = $this->query->search($data['select_all_filters'], 2000);
        } elseif (!empty($data['person_ids']) && is_array($data['person_ids'])) {
            $people = $this->query->search([
                'person_ids' => $data['person_ids'],
                'exclude_blocked' => true,
            ], 2000);
        }

        DB::transaction(function () use ($campaign, $people) {
            WhatsAppCampaignRecipient::where('campaign_id', $campaign->id)->delete();

            $seen = [];
            foreach ($people as $person) {
                $personId = (int) $person->PersonID;
                if (isset($seen[$personId])) {
                    continue;
                }
                $seen[$personId] = true;

                $phoneRaw = (string) ($person->PersonPersonalMobileNumber ?? '');
                $phone = $this->bridge->normalizeEgNumber($phoneRaw);

                $result = $this->personalizer->personalize(
                    $campaign->message_template,
                    [
                        'FirstName' => $person->FirstName ?? null,
                        'SecondName' => $person->SecondName ?? null,
                        'ThirdName' => $person->ThirdName ?? null,
                    ],
                    $campaign->missing_variable_behavior,
                    $campaign->fallback_name
                );

                $status = WhatsAppCampaignRecipient::STATUS_PENDING;
                $error = null;
                if ($result['skipped']) {
                    $status = WhatsAppCampaignRecipient::STATUS_SKIPPED;
                    $error = 'Skipped: missing personalization variables (' . implode(',', $result['missing']) . ')';
                } elseif ($phone === '+2' || strlen(preg_replace('/\D+/', '', $phone)) < 11) {
                    $status = WhatsAppCampaignRecipient::STATUS_SKIPPED;
                    $error = 'Skipped: invalid phone number';
                }

                WhatsAppCampaignRecipient::create([
                    'campaign_id' => $campaign->id,
                    'person_id' => $personId,
                    'phone' => $phone,
                    'personalized_message' => $result['message'],
                    'status' => $status,
                    'error_message' => $error,
                    'error_kind' => $status === WhatsAppCampaignRecipient::STATUS_SKIPPED ? 'permanent' : null,
                ]);
            }
        });
    }

    /**
     * Preview personalized messages for selected people (without persisting).
     *
     * @param  list<int>  $personIds
     * @return list<array<string, mixed>>
     */
    public function preview(string $template, array $personIds, string $behavior = 'fallback', ?string $fallback = null): array
    {
        $people = $this->query->search(['person_ids' => $personIds, 'exclude_blocked' => false], 500);
        $out = [];
        foreach ($people as $person) {
            $result = $this->personalizer->personalize(
                $template,
                [
                    'FirstName' => $person->FirstName ?? null,
                    'SecondName' => $person->SecondName ?? null,
                    'ThirdName' => $person->ThirdName ?? null,
                ],
                $behavior,
                $fallback
            );
            $out[] = [
                'person_id' => (int) $person->PersonID,
                'full_name' => $person->full_name,
                'phone' => $person->PersonPersonalMobileNumber,
                'message' => $result['message'],
                'missing' => $result['missing'],
                'skipped' => $result['skipped'],
            ];
        }

        return $out;
    }

    public function confirmAndStart(WhatsAppCampaign $campaign, bool $acknowledgeHighCount = false): WhatsAppCampaign
    {
        if (!$campaign->canStart()) {
            throw new RuntimeException('Campaign cannot be started.');
        }

        $sendable = $campaign->recipients()
            ->where('status', WhatsAppCampaignRecipient::STATUS_PENDING)
            ->count();

        if ($sendable === 0) {
            throw new RuntimeException('No pending recipients to send.');
        }

        if ($sendable > self::HIGH_COUNT_THRESHOLD && !$acknowledgeHighCount) {
            throw new RuntimeException(
                'Recipient count exceeds ' . self::HIGH_COUNT_THRESHOLD . '. Confirm with acknowledge_high_count=1.'
            );
        }

        if ($campaign->missing_variable_behavior === 'warn') {
            $hasMissing = $campaign->recipients()
                ->where('status', WhatsAppCampaignRecipient::STATUS_PENDING)
                ->get()
                ->contains(function (WhatsAppCampaignRecipient $r) use ($campaign) {
                    $result = $this->personalizer->personalize(
                        $campaign->message_template,
                        ['name' => ''], // force check template leftovers only via stored message
                        'empty',
                        null
                    );
                    // Re-check stored message for unresolved braces
                    return (bool) preg_match('/\{[a-zA-Z_]+\}/', (string) $r->personalized_message);
                });
            // Soft: already personalized at attach time; if warn mode and missing vars left empty, block start
            $unresolved = $campaign->recipients()
                ->where('status', WhatsAppCampaignRecipient::STATUS_PENDING)
                ->where('personalized_message', 'like', '%{%')
                ->exists();
            if ($unresolved) {
                throw new RuntimeException('Some messages still contain unresolved variables. Fix template or change missing-variable behavior.');
            }
            unset($hasMissing);
        }

        $campaign->update([
            'status' => WhatsAppCampaign::STATUS_QUEUED,
            'started_at' => now(),
        ]);

        $this->dispatchNext($campaign->fresh());

        return $campaign->fresh();
    }

    public function pause(WhatsAppCampaign $campaign): WhatsAppCampaign
    {
        if (!$campaign->canPause()) {
            throw new RuntimeException('Campaign cannot be paused.');
        }
        $campaign->update(['status' => WhatsAppCampaign::STATUS_PAUSED]);

        return $campaign->fresh();
    }

    public function resume(WhatsAppCampaign $campaign): WhatsAppCampaign
    {
        if (!$campaign->canResume()) {
            throw new RuntimeException('Campaign cannot be resumed.');
        }
        $campaign->update(['status' => WhatsAppCampaign::STATUS_RUNNING]);
        $this->dispatchNext($campaign->fresh());

        return $campaign->fresh();
    }

    public function cancel(WhatsAppCampaign $campaign): WhatsAppCampaign
    {
        if (!$campaign->canCancel()) {
            throw new RuntimeException('Campaign cannot be cancelled.');
        }

        DB::transaction(function () use ($campaign) {
            $campaign->recipients()
                ->whereIn('status', [
                    WhatsAppCampaignRecipient::STATUS_PENDING,
                    WhatsAppCampaignRecipient::STATUS_QUEUED,
                ])
                ->update([
                    'status' => WhatsAppCampaignRecipient::STATUS_CANCELLED,
                    'error_message' => 'Cancelled by admin',
                    'error_kind' => 'permanent',
                ]);

            $campaign->update([
                'status' => WhatsAppCampaign::STATUS_CANCELLED,
                'completed_at' => now(),
            ]);
        });

        return $campaign->fresh();
    }

    public function dispatchNext(WhatsAppCampaign $campaign, int $delaySeconds = 0): void
    {
        $campaign->refresh();

        if (in_array($campaign->status, [
            WhatsAppCampaign::STATUS_PAUSED,
            WhatsAppCampaign::STATUS_CANCELLED,
            WhatsAppCampaign::STATUS_COMPLETED,
            WhatsAppCampaign::STATUS_FAILED,
            WhatsAppCampaign::STATUS_DRAFT,
        ], true)) {
            return;
        }

        if ($this->hourlyCapReached($campaign)) {
            // Try again after ~2 minutes
            SendWhatsAppCampaignMessage::dispatch($campaign->id, null)
                ->delay(now()->addMinutes(2));

            return;
        }

        $next = $campaign->recipients()
            ->whereIn('status', [
                WhatsAppCampaignRecipient::STATUS_PENDING,
                WhatsAppCampaignRecipient::STATUS_QUEUED,
            ])
            ->orderBy('id')
            ->first();

        if (!$next) {
            $campaign->update([
                'status' => WhatsAppCampaign::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            return;
        }

        if ($campaign->status === WhatsAppCampaign::STATUS_QUEUED) {
            $campaign->update(['status' => WhatsAppCampaign::STATUS_RUNNING]);
        }

        $next->update([
            'status' => WhatsAppCampaignRecipient::STATUS_QUEUED,
            'scheduled_at' => now()->addSeconds($delaySeconds),
        ]);

        $pending = SendWhatsAppCampaignMessage::dispatch($campaign->id, $next->id);
        if ($delaySeconds > 0) {
            $pending->delay(now()->addSeconds($delaySeconds));
        }
    }

    public function randomDelaySeconds(WhatsAppCampaign $campaign): int
    {
        $min = max(1, (int) $campaign->min_delay_seconds);
        $max = max($min, (int) $campaign->max_delay_seconds);

        return random_int($min, $max);
    }

    public function hourlyCapReached(WhatsAppCampaign $campaign): bool
    {
        $sentLastHour = $campaign->recipients()
            ->where('status', WhatsAppCampaignRecipient::STATUS_SENT)
            ->where('sent_at', '>=', now()->subHour())
            ->count();

        return $sentLastHour >= (int) $campaign->max_messages_per_hour;
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(WhatsAppCampaign $campaign): array
    {
        $rows = $campaign->recipients()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        return [
            'total' => array_sum($rows),
            'pending' => (int) ($rows[WhatsAppCampaignRecipient::STATUS_PENDING] ?? 0)
                + (int) ($rows[WhatsAppCampaignRecipient::STATUS_QUEUED] ?? 0)
                + (int) ($rows[WhatsAppCampaignRecipient::STATUS_SENDING] ?? 0),
            'sent' => (int) ($rows[WhatsAppCampaignRecipient::STATUS_SENT] ?? 0),
            'failed' => (int) ($rows[WhatsAppCampaignRecipient::STATUS_FAILED] ?? 0),
            'skipped' => (int) ($rows[WhatsAppCampaignRecipient::STATUS_SKIPPED] ?? 0),
            'cancelled' => (int) ($rows[WhatsAppCampaignRecipient::STATUS_CANCELLED] ?? 0),
        ];
    }
}
