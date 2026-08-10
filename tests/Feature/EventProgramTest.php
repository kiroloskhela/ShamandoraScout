<?php

namespace Tests\Feature;

use App\Domain\EventProgram\EventProgramImporter;
use App\Domain\EventProgram\EventProgramMessageComposer;
use App\Domain\EventProgram\EventProgramQuery;
use App\Domain\EventProgram\GeminiImportAssistant;
use App\Domain\EventProgram\GuideTemplateBuilder;
use App\Domain\EventProgram\ImportIssueDetector;
use App\Domain\EventProgram\PersonResolver;
use App\Models\EventProgram;
use App\Models\EventProgramAssignment;
use App\Models\EventProgramDay;
use App\Models\EventProgramResource;
use App\Models\EventProgramSlot;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventProgramTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('EventProgramTest requires sqlite in-memory.');
        }

        foreach ([
            'event_program_import_sessions',
            'event_program_resources',
            'event_program_assignments',
            'event_program_slots',
            'event_program_days',
            'event_programs',
            'SeasonEvent',
            'Event',
            'EventType',
            'Season',
            'PersonInformation',
            'Roles',
            'PersonRole',
            'PersonImages',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('Gender')->nullable();
        });

        Schema::create('PersonImages', function (Blueprint $table) {
            $table->increments('PersonImageID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName');
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->integer('SeasonYear')->nullable();
        });

        Schema::create('EventType', function (Blueprint $table) {
            $table->increments('EventTypeID');
            $table->string('EventTypeName');
        });

        Schema::create('Event', function (Blueprint $table) {
            $table->increments('EventID');
            $table->unsignedInteger('EventTypeID');
            $table->string('EventName');
            $table->date('EventStartDate')->nullable();
            $table->date('EventEndDate')->nullable();
        });

        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('SeasonID');
            $table->unsignedInteger('EventID');
        });

        Schema::create('event_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('SeasonEventID')->unique();
            $table->string('title');
            $table->string('status', 20)->default('draft');
            $table->text('intro_template')->nullable();
            $table->text('outro_template')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamp('last_refreshed_at')->nullable();
            $table->json('known_people_json')->nullable();
            $table->timestamps();
        });

        Schema::create('event_program_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_program_id');
            $table->unsignedSmallInteger('day_number');
            $table->date('date')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('event_program_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_program_day_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity_label');
            $table->string('slot_kind', 20)->default('general');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_program_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_program_slot_id');
            $table->unsignedInteger('person_id');
            $table->string('mission_text')->nullable();
            $table->string('team_label')->nullable();
            $table->timestamps();
        });

        Schema::create('event_program_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_program_id');
            $table->unsignedBigInteger('event_program_day_id')->nullable();
            $table->unsignedBigInteger('event_program_slot_id')->nullable();
            $table->string('kind', 20);
            $table->string('title');
            $table->text('url')->nullable();
            $table->string('slot_label')->nullable();
            $table->timestamps();
        });

        Schema::create('event_program_import_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_program_id')->nullable();
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('created_by')->nullable();
            $table->string('status', 30)->default('pending_review');
            $table->string('source', 30)->default('upload');
            $table->json('parsed_json')->nullable();
            $table->json('issues_json')->nullable();
            $table->json('questions_json')->nullable();
            $table->json('answers_json')->nullable();
            $table->timestamps();
        });

        DB::table('Roles')->insert(['RoleID' => 1, 'RoleName' => 'SuperAdmin', 'RoleDescription' => null]);
        DB::table('PersonInformation')->insert([
            ['PersonID' => 1, 'ShamandoraCode' => 'SH-00001', 'FirstName' => 'كيرلس', 'SecondName' => 'أمجد', 'ThirdName' => 'خله', 'Gender' => 'm'],
            ['PersonID' => 2, 'ShamandoraCode' => 'SH-00002', 'FirstName' => 'ايريني', 'SecondName' => 'ناير', 'ThirdName' => 'غبريال', 'Gender' => 'f'],
        ]);
        DB::table('PersonRole')->insert(['PersonRoleID' => 1, 'PersonID' => 1, 'RoleID' => 1, 'RequestPersonID' => null]);
        DB::table('Season')->insert(['SeasonID' => 1, 'SeasonName' => '2026', 'SeasonYear' => 2026]);
        DB::table('EventType')->insert(['EventTypeID' => 1, 'EventTypeName' => 'معسكر مجمع']);
        DB::table('Event')->insert([
            'EventID' => 1,
            'EventTypeID' => 1,
            'EventName' => 'Ready Steady Go',
            'EventStartDate' => '2026-07-01',
            'EventEndDate' => '2026-07-04',
        ]);
        DB::table('SeasonEvent')->insert(['SeasonEventID' => 10, 'SeasonID' => 1, 'EventID' => 1]);

        config(['event_program.gemini.api_key' => '']);
    }

    public function test_guide_template_is_generated(): void
    {
        $path = storage_path('app/tmp/test_guide_'.uniqid().'.xlsx');
        app(GuideTemplateBuilder::class)->writeGuideXlsx($path);
        $this->assertFileExists($path);
        $this->assertGreaterThan(1000, filesize($path));
        @unlink($path);
    }

    public function test_import_xlsx_and_commit_without_ai_questions_when_people_match(): void
    {
        $path = storage_path('app/tmp/test_import_'.uniqid().'.xlsx');
        app(GuideTemplateBuilder::class)->writeGuideXlsx($path);

        // Patch Day 1 sample codes already match SH-00001 / SH-00002
        $importer = app(EventProgramImporter::class);
        $session = $importer->startFromXlsx(10, $path, 1, 'upload');
        $this->assertContains($session->status, ['ready', 'pending_review']);

        if ($session->status === 'pending_review') {
            $answers = [];
            foreach ($session->questions_json ?? [] as $q) {
                $opts = $q['options'] ?? [];
                $answers[$q['id']] = $opts[0]['value'] ?? 'skip';
            }
            $importer->answer($session, $answers);
        }

        $program = $importer->commit($session->fresh());
        $this->assertSame('draft', $program->status);
        $this->assertGreaterThanOrEqual(1, $program->days()->count());
        @unlink($path);
    }

    public function test_publish_gate_and_my_program_visibility(): void
    {
        $program = EventProgram::create([
            'SeasonEventID' => 10,
            'title' => 'Camp',
            'status' => EventProgram::STATUS_DRAFT,
        ]);
        $day = EventProgramDay::create([
            'event_program_id' => $program->id,
            'day_number' => 1,
            'label' => 'يوم 1',
        ]);
        $slot = EventProgramSlot::create([
            'event_program_day_id' => $day->id,
            'start_time' => '06:00',
            'end_time' => '06:30',
            'activity_label' => 'تجمع',
            'slot_kind' => 'general',
            'sort_order' => 0,
        ]);
        EventProgramAssignment::create([
            'event_program_slot_id' => $slot->id,
            'person_id' => 1,
            'mission_text' => 'التجمع',
        ]);

        $query = app(EventProgramQuery::class);
        $this->assertNull($query->myProgramPayload(10, 1));

        $program->status = EventProgram::STATUS_PUBLISHED;
        $program->save();

        $payload = $query->myProgramPayload(10, 1);
        $this->assertNotNull($payload);
        $this->assertCount(1, $payload['days']);
        $this->assertSame('التجمع', $payload['days'][0]['slots'][0]['mission_text']);

        $this->assertNull($query->myProgramPayload(10, 2));
    }

    public function test_message_composer_includes_mission(): void
    {
        $program = EventProgram::create([
            'SeasonEventID' => 10,
            'title' => 'Ready Steady Go',
            'status' => EventProgram::STATUS_PUBLISHED,
            'intro_template' => "أهلاً يا {title} {name}\nده برنامجك لليوم {day} في {event_name}\n",
            'outro_template' => "\nشكراً",
        ]);
        $day = EventProgramDay::create(['event_program_id' => $program->id, 'day_number' => 1, 'label' => 'يوم 1']);
        $slot = EventProgramSlot::create([
            'event_program_day_id' => $day->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'activity_label' => 'العاب',
            'slot_kind' => 'games',
            'sort_order' => 0,
        ]);
        EventProgramAssignment::create([
            'event_program_slot_id' => $slot->id,
            'person_id' => 1,
            'mission_text' => 'العاب',
        ]);

        $composed = app(EventProgramMessageComposer::class)->composeDayMessage($program->fresh(['days.slots.assignments', 'resources']), 1, 1);
        $this->assertNotNull($composed);
        $this->assertStringContainsString('العاب', $composed['text']);
        $this->assertStringContainsString('كيرلس', $composed['text']);
    }

    public function test_resource_matcher_specific_game_and_lecture_rules(): void
    {
        $program = EventProgram::create([
            'SeasonEventID' => 10,
            'title' => 'Camp',
            'status' => EventProgram::STATUS_PUBLISHED,
        ]);
        $day = EventProgramDay::create([
            'event_program_id' => $program->id,
            'day_number' => 1,
            'label' => 'يوم 1',
        ]);
        $gamesSlot = EventProgramSlot::create([
            'event_program_day_id' => $day->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'activity_label' => 'العاب',
            'slot_kind' => 'games',
            'sort_order' => 0,
        ]);
        $lectureSlot = EventProgramSlot::create([
            'event_program_day_id' => $day->id,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'activity_label' => 'كشفي',
            'slot_kind' => 'lecture',
            'sort_order' => 1,
        ]);
        $genericLectureSlot = EventProgramSlot::create([
            'event_program_day_id' => $day->id,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'activity_label' => 'توصيل هدف',
            'slot_kind' => 'lecture',
            'sort_order' => 2,
        ]);

        EventProgramResource::create([
            'event_program_id' => $program->id,
            'event_program_day_id' => $day->id,
            'kind' => 'game',
            'title' => 'Instagame (Pose & Guess)',
            'url' => 'https://example.com/instagame',
        ]);
        EventProgramResource::create([
            'event_program_id' => $program->id,
            'event_program_day_id' => $day->id,
            'kind' => 'game',
            'title' => 'WhatsApp Relay',
            'url' => 'https://example.com/relay',
        ]);
        EventProgramResource::create([
            'event_program_id' => $program->id,
            'event_program_day_id' => $day->id,
            'kind' => 'lecture',
            'title' => 'جمع و صفافير',
            'url' => 'https://example.com/safafeer',
        ]);
        EventProgramResource::create([
            'event_program_id' => $program->id,
            'event_program_day_id' => $day->id,
            'kind' => 'lecture',
            'title' => 'نيران و طهي خلوي',
            'url' => 'https://example.com/fire',
        ]);

        EventProgramAssignment::create([
            'event_program_slot_id' => $gamesSlot->id,
            'person_id' => 1,
            'mission_text' => 'Instagame (Pose & Guess)',
        ]);
        EventProgramAssignment::create([
            'event_program_slot_id' => $lectureSlot->id,
            'person_id' => 1,
            'mission_text' => 'كشفي ( جمع و صفافير )',
        ]);
        EventProgramAssignment::create([
            'event_program_slot_id' => $genericLectureSlot->id,
            'person_id' => 1,
            'mission_text' => 'توصيل هدف',
        ]);

        // Person 2: generic games only — should get no game dump
        EventProgramAssignment::create([
            'event_program_slot_id' => $gamesSlot->id,
            'person_id' => 2,
            'mission_text' => 'العاب',
        ]);

        $payload = app(EventProgramQuery::class)->myProgramPayload(10, 1);
        $this->assertNotNull($payload);
        $slots = collect($payload['days'][0]['slots'])->keyBy('activity_label');

        $gameLinks = $slots['العاب']['resources'];
        $this->assertCount(1, $gameLinks);
        $this->assertSame('Instagame (Pose & Guess)', $gameLinks[0]['title']);

        $specificLecture = $slots['كشفي']['resources'];
        $this->assertCount(1, $specificLecture);
        $this->assertSame('جمع و صفافير', $specificLecture[0]['title']);

        $genericLecture = $slots['توصيل هدف']['resources'];
        $this->assertCount(2, $genericLecture);

        $payload2 = app(EventProgramQuery::class)->myProgramPayload(10, 2);
        $games2 = collect($payload2['days'][0]['slots'])->firstWhere('activity_label', 'العاب')['resources'];
        $this->assertSame([], $games2);
    }

    public function test_gemini_assistant_falls_back_to_rule_based_without_key(): void
    {
        config(['event_program.gemini.api_key' => '']);
        $assistant = app(GeminiImportAssistant::class);
        $questions = $assistant->buildQuestions([
            'hard' => [],
            'soft' => [[
                'code' => 'person_unresolved',
                'name' => 'شخص مجهول',
                'day_number' => 1,
                'leader_index' => 0,
                'candidates' => [
                    ['person_id' => 1, 'name' => 'كيرلس أمجد خله', 'code' => 'SH-00001'],
                ],
            ]],
        ]);

        $this->assertNotEmpty($questions);
        $this->assertSame('person', $questions[0]['type']);
    }

    public function test_gemini_assistant_parses_mocked_http_response(): void
    {
        config([
            'event_program.gemini.api_key' => 'test-key',
            'event_program.gemini.model' => 'gemini-2.5-flash',
            'event_program.gemini.endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'questions' => [[
                                    'id' => 'ai_1',
                                    'code' => 'resource_same_title_multi_day',
                                    'prompt' => 'هل Catch the flag نفس اللعبة في اليومين؟',
                                    'type' => 'choice',
                                    'options' => [
                                        ['value' => 'same', 'label' => 'نفسها'],
                                        ['value' => 'different', 'label' => 'مختلفة'],
                                    ],
                                ]],
                            ], JSON_UNESCAPED_UNICODE),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $questions = app(GeminiImportAssistant::class)->buildQuestions([
            'hard' => [],
            'soft' => [['code' => 'resource_same_title_multi_day', 'title' => 'Catch the flag']],
        ]);

        $this->assertCount(1, $questions);
        $this->assertSame('ai_1', $questions[0]['id']);
        $this->assertStringContainsString('Catch the flag', $questions[0]['prompt']);
    }

    public function test_person_resolver_matches_shamandora_code(): void
    {
        $r = app(PersonResolver::class)->resolve(null, 'SH-00002', 'اسم غلط');
        $this->assertSame('matched', $r['status']);
        $this->assertSame(2, $r['person_id']);
    }

    public function test_issue_detector_flags_unmatched_person(): void
    {
        $issues = app(ImportIssueDetector::class)->detect([
            'meta' => [],
            'days' => [[
                'day_number' => 1,
                'slots' => [['start_time' => '06:00', 'end_time' => '06:30', 'activity_label' => 'تجمع', 'slot_kind' => 'general']],
                'leaders' => [[
                    'person_id' => null,
                    'shamandora_code' => null,
                    'name' => 'اسم مش موجود خالص',
                    'missions' => [0 => 'تجمع'],
                ]],
            ]],
            'resources' => [],
        ]);

        $this->assertNotEmpty($issues['soft']);
        $this->assertSame('person_unresolved', $issues['soft'][0]['code']);
    }
}
