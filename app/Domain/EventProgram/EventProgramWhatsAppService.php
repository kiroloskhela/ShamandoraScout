<?php

namespace App\Domain\EventProgram;

use App\Domain\WhatsApp\WhatsAppCampaignService;
use App\Models\EventProgram;
use App\Models\EventProgramAssignment;
use App\Models\WhatsAppCampaign;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EventProgramWhatsAppService
{
    public function __construct(
        private readonly EventProgramMessageComposer $composer,
        private readonly WhatsAppCampaignService $campaigns,
    ) {}

    /**
     * Build a WhatsApp campaign draft with personalized full-program messages.
     */
    public function createCampaignDraft(EventProgram $program, int $createdBy, ?int $dayNumber = null): WhatsAppCampaign
    {
        $personIds = EventProgramAssignment::query()
            ->whereHas('slot.day', fn ($q) => $q->where('event_program_id', $program->id))
            ->distinct()
            ->pluck('person_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($personIds === []) {
            throw new RuntimeException('لا يوجد قادة عليهم مهام لإرسال الرسائل.');
        }

        $phones = DB::table('PersonPhoneNumbers')
            ->whereIn('PersonID', $personIds)
            ->where(function ($q) {
                $q->whereNull('DoNotContact')->orWhere('DoNotContact', 0);
            })
            ->where(function ($q) {
                $q->whereNull('WhatsAppConsent')->orWhere('WhatsAppConsent', 1);
            })
            ->pluck('PersonPersonalMobileNumber', 'PersonID');

        $blacklist = DB::table('PersonBlackList')->whereIn('PersonID', $personIds)->pluck('PersonID')->all();
        $blacklist = array_flip($blacklist);

        $rows = [];
        foreach ($personIds as $personId) {
            if (isset($blacklist[$personId])) {
                continue;
            }
            $phoneRaw = (string) ($phones[$personId] ?? '');
            if ($phoneRaw === '') {
                continue;
            }
            $phone = $this->normalizeEgyptPhone($phoneRaw);
            $message = $dayNumber
                ? ($this->composer->composeDayMessage($program, $personId, $dayNumber)['text'] ?? null)
                : $this->composer->composeFullMessage($program, $personId);

            if (! $message || $phone === '') {
                continue;
            }

            $rows[] = [
                'phone' => $phone,
                'message' => $message,
                'person_id' => $personId,
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('لا توجد أرقام واتساب صالحة للمستلمين.');
        }

        return $this->campaigns->createDraftFromCsvRows([
            'name' => 'برنامج: '.$program->title.($dayNumber ? " — يوم {$dayNumber}" : ''),
        ], $rows, $createdBy);
    }

    private function normalizeEgyptPhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (str_starts_with($digits, '20') && strlen($digits) >= 12) {
            return '+'.$digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '+2'.$digits;
        }
        if (strlen($digits) === 10) {
            return '+20'.$digits;
        }

        return $digits !== '' ? '+'.$digits : '';
    }
}
