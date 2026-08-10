<?php

namespace App\Domain\EventProgram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class GeminiImportAssistant
{
    /**
     * @param  array{hard: list<array>, soft: list<array>}  $issues
     * @param  array<string, mixed>  $context
     * @return list<array{id: string, code: string, prompt: string, type: string, options?: list<array{value: string, label: string}>}>
     */
    public function buildQuestions(array $issues, array $context = []): array
    {
        $soft = array_slice($issues['soft'] ?? [], 0, 40);
        if ($soft === []) {
            return [];
        }

        $apiKey = (string) config('event_program.gemini.api_key');
        if ($apiKey === '') {
            return $this->ruleBasedQuestions($soft);
        }

        try {
            $aiQuestions = $this->askGemini($soft, $context);
            if ($aiQuestions !== []) {
                return $aiQuestions;
            }
        } catch (\Throwable) {
            // fall through
        }

        return $this->ruleBasedQuestions($soft);
    }

    /**
     * @param  list<array>  $soft
     * @param  array<string, mixed>  $context
     * @return list<array>
     */
    private function askGemini(array $soft, array $context): array
    {
        $model = (string) config('event_program.gemini.model', 'gemini-2.5-flash');
        $endpoint = rtrim((string) config('event_program.gemini.endpoint'), '/');
        $apiKey = (string) config('event_program.gemini.api_key');
        $url = "{$endpoint}/{$model}:generateContent?key={$apiKey}";

        $system = <<<'TXT'
أنت مساعد استيراد برنامج معسكر في نظام شمندورة سكاوت.
السياق: لكل قائد مهام يومية (missions) على فقرات زمنية مشتركة؛ يوجد أيضاً روابط ألعاب ومحاضرات.
المطلوب: أعد JSON فقط بالشكل:
{"questions":[{"id":"q1","code":"...","prompt":"سؤال بالعربي","type":"choice|text|person","options":[{"value":"...","label":"..."}]}]}
قواعد:
- اسأل فقط عن القضايا التي تمنع الاستيراد الآمن.
- فضّل الاختيار المتعدد.
- لا تخترع PersonID غير موجود في المرشحين.
- type=person عندما نحتاج اختيار شخص من candidates.
TXT;

        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => $system."\n\nCONTEXT:\n".json_encode($context, JSON_UNESCAPED_UNICODE)
                        ."\n\nSOFT_ISSUES:\n".json_encode($soft, JSON_UNESCAPED_UNICODE),
                ]],
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout((int) config('event_program.gemini.timeout', 45))
            ->post($url, $payload);

        if (! $response->successful()) {
            return [];
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
        $decoded = json_decode((string) $text, true);
        if (! is_array($decoded)) {
            return [];
        }

        $questions = $decoded['questions'] ?? $decoded;
        if (! is_array($questions)) {
            return [];
        }

        $out = [];
        foreach ($questions as $i => $q) {
            if (! is_array($q) || empty($q['prompt'])) {
                continue;
            }
            $out[] = [
                'id' => (string) ($q['id'] ?? ('ai_'.$i)),
                'code' => (string) ($q['code'] ?? 'ai'),
                'prompt' => (string) $q['prompt'],
                'type' => (string) ($q['type'] ?? 'choice'),
                'options' => $q['options'] ?? [],
                'meta' => $q['meta'] ?? [],
            ];
        }

        return $out;
    }

    /**
     * @param  list<array>  $soft
     * @return list<array>
     */
    private function ruleBasedQuestions(array $soft): array
    {
        $questions = [];
        foreach ($soft as $i => $issue) {
            $code = (string) ($issue['code'] ?? 'unknown');
            $id = 'rb_'.$i.'_'.Str::slug($code, '_');

            if ($code === 'person_unresolved') {
                $options = [];
                foreach ($issue['candidates'] ?? [] as $c) {
                    $options[] = [
                        'value' => (string) $c['person_id'],
                        'label' => ($c['name'] ?? '').' — '.($c['code'] ?? ('ID '.$c['person_id'])),
                    ];
                }
                $options[] = ['value' => 'skip', 'label' => 'تخطّي هذا القائد'];
                $questions[] = [
                    'id' => $id,
                    'code' => $code,
                    'prompt' => 'مفيش مطابقة واضحة لـ «'.($issue['name'] ?? '').'». تقصد مين؟',
                    'type' => 'person',
                    'options' => $options,
                    'meta' => [
                        'day_number' => $issue['day_number'] ?? null,
                        'leader_index' => $issue['leader_index'] ?? null,
                        'name' => $issue['name'] ?? null,
                        'shamandora_code' => $issue['shamandora_code'] ?? null,
                    ],
                ];
                continue;
            }

            if ($code === 'resource_same_title_multi_day') {
                $questions[] = [
                    'id' => $id,
                    'code' => $code,
                    'prompt' => $issue['message'] ?? 'هل هذا نفس المورد عبر الأيام؟',
                    'type' => 'choice',
                    'options' => [
                        ['value' => 'same', 'label' => 'نفس المورد (نوحّد اللينك إن أمكن)'],
                        ['value' => 'different', 'label' => 'مختلفة — اتركها كما هي'],
                    ],
                    'meta' => [
                        'title' => $issue['title'] ?? null,
                        'occurrences' => $issue['occurrences'] ?? [],
                    ],
                ];
                continue;
            }

            if ($code === 'resource_missing_url') {
                $questions[] = [
                    'id' => $id,
                    'code' => $code,
                    'prompt' => '«'.($issue['title'] ?? '').'» من غير لينك. نكمّل من غيره؟',
                    'type' => 'choice',
                    'options' => [
                        ['value' => 'continue', 'label' => 'كمّل من غير لينك'],
                        ['value' => 'skip', 'label' => 'احذف هذا المورد'],
                    ],
                    'meta' => ['resource_index' => $issue['resource_index'] ?? null],
                ];
            }
        }

        return $questions;
    }
}
