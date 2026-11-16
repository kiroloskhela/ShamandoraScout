<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiveFormDraftTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('MarhalaEntryQuestions', function (Blueprint $table) {
            $table->increments('QuestionID');
            $table->unsignedInteger('QetaaID');
            $table->unsignedTinyInteger('NotToBeShown')->default(0);
            $table->unsignedTinyInteger('IsRequired')->default(0);
            $table->string('QuestionText')->nullable();
            $table->string('RequiredAnswerType')->nullable();
        });
    }

    public function test_missing_required_question_returns_to_the_form_with_answers(): void
    {
        $requiredId = DB::table('MarhalaEntryQuestions')->insertGetId([
            'QetaaID' => 3,
            'NotToBeShown' => 0,
            'IsRequired' => 1,
            'QuestionText' => 'Why do you want to join?',
            'RequiredAnswerType' => 'OpenQuestion',
        ]);

        $optionalId = DB::table('MarhalaEntryQuestions')->insertGetId([
            'QetaaID' => 3,
            'NotToBeShown' => 0,
            'IsRequired' => 0,
            'QuestionText' => 'Anything else?',
            'RequiredAnswerType' => 'OpenQuestion',
        ]);

        $response = $this->withSession([
            'liveform.step1' => [
                'qetaa_id' => 3,
                'qetaa_name' => 'Cubs',
                'gender' => 'Male',
                'sana_marhala_id' => 1,
                'sana_marhala_name' => '2026',
            ],
            'liveform.step2' => [
                'first_name' => 'كيرلس',
                'second_name' => 'اختبار',
                'third_name' => 'تجربة',
                'fourth_name' => null,
                'input_raqam_qawmy' => '30001011234567',
            ],
        ])->post(route('person.entry-questions-submit-liveform'), [
            $requiredId => '',
            $optionalId => 'I can swim',
        ]);

        $response->assertRedirect(route('person.entry-questions-liveform'));
        $response->assertSessionHasErrors([(string) $requiredId]);

        $this->get(route('person.entry-questions-liveform'))
            ->assertOk()
            ->assertSee('Why do you want to join?', false)
            ->assertSee('value="I can swim"', false)
            ->assertSee(__('Answer this question: :question', ['question' => 'Why do you want to join?']), false)
            ->assertSee('shamandora.liveform.questions', false)
            ->assertDontSee('Go back to the previous page', false);
    }

    public function test_step_pages_include_the_local_draft(): void
    {
        foreach (['Marhala', 'RotbaInformation', 'QuestionsTypes', 'BloodType', 'Manteqa', 'Districts', 'Faculty', 'University', 'Folar'] as $table) {
            Schema::create($table, function (Blueprint $blueprint) {
                $blueprint->increments('id');
            });
        }

        Schema::create('SanaMarhala', function (Blueprint $table) {
            $table->increments('SanaMarhalaID');
            $table->string('SanaMarhalaName')->nullable();
        });

        DB::table('SanaMarhala')->insert(['SanaMarhalaName' => '2026']);

        $this->get(route('person.liveform-create'))
            ->assertOk()
            ->assertSee('shamandora.liveform.step1', false);

        $this->withSession([
            'liveform.step1' => [
                'sana_marhala_id' => 8,
                'sana_marhala_name' => '2026',
                'gender' => 'Male',
                'qetaa_id' => null,
                'qetaa_name' => null,
                'available_qetaat' => [],
            ],
        ])->get(route('person.liveform-step2'))
            ->assertOk()
            ->assertSee('shamandora.liveform.step2', false)
            ->assertSee('window.__liveformWizardStep', false)
            ->assertSee('24 * 60 * 60 * 1000', false)
            ->assertDontSee('7 * 24 * 60 * 60 * 1000', false);
    }

    public function test_wrong_national_id_stays_on_the_form_with_a_clear_message(): void
    {
        $response = $this->withSession([
            'liveform.step1' => [
                'sana_marhala_id' => 1,
                'sana_marhala_name' => '2026',
                'gender' => 'Male',
                'qetaa_id' => 3,
                'qetaa_name' => 'Cubs',
                'available_qetaat' => [
                    ['QetaaID' => 3, 'QetaaName' => 'Cubs', 'is_full' => false],
                ],
            ],
        ])->post(route('person.liveform-step2-save'), [
            'first_name' => 'John',
            'second_name' => 'كيرلس',
            'third_name' => 'عطا',
            'birthdate_input' => '2030-01-01',
            'joining_year_input' => '2026',
            'input_raqam_qawmy' => '١٢٣',
            'blood_type_input' => '1',
            'personal_phone_number' => '0100',
            'father_phone_number' => '12345',
            'home_phone_number' => '12',
            'inputFacebookLink' => 'not a link',
            'building_number' => '1',
            'floor_number' => '2',
            'appartment_number' => '3',
            'sub_street_name' => 'الشارع',
            'manteqa_id' => '1',
            'district_id' => '1',
            'qetaa_id' => '3',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'first_name' => __('Please enter Arabic letters only'),
            'birthdate_input' => __('The date of birth cannot be in the future.'),
            'input_raqam_qawmy' => __('The national ID must be 14 digits.'),
            'personal_phone_number' => __('The mobile number must be 11 digits.'),
            'father_phone_number' => __('The mobile number must be 11 digits.'),
            'home_phone_number' => __('Enter the landline as digits only, from 5 to 11 digits.'),
            'inputFacebookLink' => __('The URL must be a valid http:// or https:// address.'),
        ]);
    }
}
