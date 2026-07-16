<?php

namespace App\Domain\Person;

use App\Support\ShamandoraCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Multi-table person profile updates (directory edit form).
 */
class PersonProfileService
{
    public function exists(int $personId): bool
    {
        return DB::table('PersonInformation')->where('PersonID', $personId)->exists();
    }

    public function raqamQawmyTaken(string $raqamQawmy, int $excludePersonId): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS counts FROM PersonInformation WHERE RaqamQawmy = ? AND PersonID != ?',
            [$raqamQawmy, $excludePersonId]
        );

        return ($row->counts ?? 0) > 0;
    }

    /**
     * @param  array<string, mixed>  $data  Validated request fields (same keys as the create form)
     * @return array{person_id:int,password:string}
     */
    public function createProfile(array $data, int $requestPersonId): array
    {
        return DB::transaction(function () use ($data, $requestPersonId) {
            $personId = (int) DB::table('PersonInformation')->insertGetId([
                'ShamandoraCode' => bin2hex(random_bytes(5)),
                'FirstName' => $data['first_name'] ?? null,
                'SecondName' => $data['second_name'] ?? null,
                'ThirdName' => $data['third_name'] ?? null,
                'FourthName' => $data['fourth_name'] ?? null,
                'Gender' => $data['gender'] ?? null,
                'DateOfBirth' => $data['birthdate_input'] ?? null,
                'RaqamQawmy' => $data['input_raqam_qawmy'] ?? null,
                'ScoutJoiningYear' => $data['joining_year_input'] ?? null,
                'BloodTypeID' => $data['blood_type_input'] ?? null,
                'FacebookProfileURL' => $data['inputFacebookLink'] ?? null,
                'InstagramProfileURL' => $data['inputInstagramLink'] ?? null,
                'PersonalEmail' => $data['email_input'] ?? null,
                'RequestPersonID' => $data['RequestPersonID'] ?? $requestPersonId,
            ], 'PersonID');

            DB::table('PersonInformation')->where('PersonID', $personId)->update([
                'ShamandoraCode' => ShamandoraCode::forPersonId($personId),
            ]);

            DB::table('PersonPhoneNumbers')->insert([
                'PersonID' => $personId,
                'PersonPersonalMobileNumber' => $data['personal_phone_number'] ?? null,
                'FatherMobileNumber' => $data['father_phone_number'] ?? null,
                'MotherMobileNumber' => $data['mother_phone_number'] ?? null,
                'HomePhoneNumber' => $data['home_phone_number'] ?? null,
                'IsOPersonalPhoneNumberHavingWhatsapp' => $data['has_whatsapp'] ?? null,
            ]);

            DB::table('PersonJob')->insert([
                'PersonID' => $personId,
                'JobName' => $data['person_job'] ?? null,
                'WorkPlace' => $data['person_job_place'] ?? null,
            ]);

            DB::table('PersonLearningInformation')->insert([
                'PersonID' => $personId,
                'SchoolName' => $data['school_name'] ?? ($data['person_school'] ?? null),
                'SchoolGraduationYear' => $data['school_grad_year'] ?? null,
                'FacultyID' => $data['person_faculty'] ?? null,
                'UniversityID' => $data['person_university'] ?? null,
                'ActualFacultyGraduationYear' => $data['university_grad_year'] ?? null,
            ]);

            if (! empty($data['rotba_kashfeyya_id'])) {
                DB::table('PersonRotbaKashfeyya')->insert([
                    'PersonID' => $personId,
                    'RotbaID' => $data['rotba_kashfeyya_id'],
                ]);
            }

            DB::table('PersonQetaa')->insert([
                'PersonID' => $personId,
                'QetaaID' => $data['qetaa_id'] ?? null,
            ]);

            if (! empty($data['betaka_id'])) {
                DB::table('PersonEgazetBetakatTaqaddom')->insert([
                    'PersonID' => $personId,
                    'EgazetBetakatTaqaddomID' => $data['betaka_id'],
                ]);
            }

            DB::table('PersonSanaMarhala')->insert([
                'PersonID' => $personId,
                'SanaMarhalaID' => $data['sana_marhala_id'] ?? null,
            ]);

            DB::table('PersonSpiritualFatherInformation')->insert([
                'PersonID' => $personId,
                'SpiritualFatherName' => $data['spiritual_father'] ?? null,
                'SpiritualFatherChurchName' => $data['spiritual_father_church'] ?? null,
            ]);

            $password = $this->temporaryPassword();

            DB::table('PersonSystemPassword')->insert([
                'PersonID' => $personId,
                'Password' => Hash::make($password),
            ]);

            DB::table('PersonalPhysicalAddress')->insert([
                'PersonID' => $personId,
                'BuildingNumber' => $data['building_number'] ?? null,
                'FloorNumber' => $data['floor_number'] ?? null,
                'AppartmentNumber' => $data['appartment_number'] ?? null,
                'MainStreetName' => $data['main_street_name'] ?? null,
                'SubStreetName' => $data['sub_street_name'] ?? null,
                'ManteqaID' => $data['manteqa_id'] ?? null,
                'DistrictID' => ($data['district_id'] ?? null) ?: 1,
                'NearestLandmark' => $data['nearest_landmark'] ?? null,
            ]);

            return [
                'person_id' => $personId,
                'password' => $password,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $data  Validated request fields (same keys as the edit form)
     * @param  array<int|string, mixed>|null  $questions  questionId => answer
     */
    public function updateProfile(
        int $personId,
        array $data,
        ?UploadedFile $personalImage = null,
        ?UploadedFile $scoutImage = null,
        ?array $questions = null,
        bool $questionsProvided = false,
    ): void {
        DB::transaction(function () use ($personId, $data, $personalImage, $scoutImage, $questions, $questionsProvided) {
            $oldImages = DB::table('PersonImages')->where('PersonID', $personId)->first();

            $personSystemImagePath = $oldImages->PersonSystemImagePath ?? null;
            $scoutOfficialUniformImagePath = $oldImages->ScoutOfficialUniformImagePath ?? null;

            if ($personalImage) {
                if ($personSystemImagePath && Storage::disk('public')->exists($personSystemImagePath)) {
                    Storage::disk('public')->delete($personSystemImagePath);
                }
                $personSystemImagePath = $personalImage->store('persons/personal', 'public');
            }

            if ($scoutImage) {
                if ($scoutOfficialUniformImagePath && Storage::disk('public')->exists($scoutOfficialUniformImagePath)) {
                    Storage::disk('public')->delete($scoutOfficialUniformImagePath);
                }
                $scoutOfficialUniformImagePath = $scoutImage->store('persons/scout', 'public');
            }

            $personUpdate = [
                'FirstName' => $data['first_name'] ?? null,
                'SecondName' => $data['second_name'] ?? null,
                'ThirdName' => $data['third_name'] ?? null,
                'FourthName' => $data['fourth_name'] ?? null,
                'Gender' => $data['gender'] ?? null,
                'DateOfBirth' => $data['birthdate_input'] ?? null,
                'ScoutJoiningYear' => $data['joining_year_input'] ?? null,
                'BloodTypeID' => $data['blood_type_input'] ?? null,
                'FacebookProfileURL' => $data['inputFacebookLink'] ?? null,
                'InstagramProfileURL' => $data['inputInstagramLink'] ?? null,
                'PersonalEmail' => $data['email_input'] ?? null,
            ];

            // National ID is locked on self-profile; only update when explicitly provided.
            if (array_key_exists('input_raqam_qawmy', $data)) {
                $personUpdate['RaqamQawmy'] = $data['input_raqam_qawmy'];
            }

            if (array_key_exists('RequestPersonID', $data)) {
                $personUpdate['RequestPersonID'] = $data['RequestPersonID'];
            }

            DB::table('PersonInformation')
                ->where('PersonID', $personId)
                ->update($personUpdate);

            $this->upsertFiltered('PersonPhoneNumbers', $personId, [
                'PersonPersonalMobileNumber' => $data['personal_phone_number'] ?? null,
                'FatherMobileNumber' => $data['father_phone_number'] ?? null,
                'MotherMobileNumber' => $data['mother_phone_number'] ?? null,
                'HomePhoneNumber' => $data['home_phone_number'] ?? null,
                'IsOPersonalPhoneNumberHavingWhatsapp' => $data['has_whatsapp'] ?? null,
            ]);

            $this->upsertFiltered('PersonJob', $personId, [
                'JobName' => $data['person_job'] ?? null,
                'WorkPlace' => $data['person_job_place'] ?? null,
            ]);

            $this->upsertFiltered('PersonLearningInformation', $personId, [
                'SchoolName' => $data['school_name'] ?? null,
                'SchoolGraduationYear' => $data['school_grad_year'] ?? null,
                'FacultyID' => $data['person_faculty'] ?? null,
                'UniversityID' => $data['person_university'] ?? null,
                'ActualFacultyGraduationYear' => $data['university_grad_year'] ?? null,
            ]);

            $this->upsertOrDelete('PersonRotbaKashfeyya', $personId, 'RotbaID', $data['rotba_kashfeyya_id'] ?? null);
            $this->upsertOrDelete('PersonEgazetBetakatTaqaddom', $personId, 'EgazetBetakatTaqaddomID', $data['betaka_id'] ?? null);
            $this->upsertOrDelete('PersonSanaMarhala', $personId, 'SanaMarhalaID', $data['sana_marhala_id'] ?? null);

            $this->upsertFiltered('PersonSpiritualFatherInformation', $personId, [
                'SpiritualFatherName' => $data['spiritual_father'] ?? null,
                'SpiritualFatherChurchName' => $data['spiritual_father_church'] ?? null,
            ]);

            $this->upsertFiltered('PersonalPhysicalAddress', $personId, [
                'BuildingNumber' => $data['building_number'] ?? null,
                'FloorNumber' => $data['floor_number'] ?? null,
                'AppartmentNumber' => $data['appartment_number'] ?? null,
                'MainStreetName' => $data['main_street_name'] ?? null,
                'SubStreetName' => $data['sub_street_name'] ?? null,
                'NearestLandmark' => $data['nearest_landmark'] ?? null,
                'ManteqaID' => $data['manteqa_id'] ?? null,
                'DistrictID' => $data['district_id'] ?? null,
            ]);

            if ($personalImage || $scoutImage) {
                DB::table('PersonImages')->updateOrInsert(
                    ['PersonID' => $personId],
                    [
                        'PersonSystemImagePath' => $personSystemImagePath,
                        'ScoutOfficialUniformImagePath' => $scoutOfficialUniformImagePath,
                    ]
                );
            }

            if ($questionsProvided) {
                foreach (($questions ?? []) as $questionId => $answer) {
                    if ($answer === null || trim((string) $answer) === '') {
                        DB::table('PersonEntryQuestions')
                            ->where('PersonID', $personId)
                            ->where('QuestionID', $questionId)
                            ->delete();

                        continue;
                    }

                    DB::table('PersonEntryQuestions')->updateOrInsert(
                        [
                            'PersonID' => $personId,
                            'QuestionID' => $questionId,
                        ],
                        [
                            'Answer' => $answer,
                        ]
                    );
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertFiltered(string $table, int $personId, array $data): void
    {
        $filtered = array_filter($data, fn ($value) => $value !== null && $value !== '');
        if ($filtered === []) {
            return;
        }

        DB::table($table)->updateOrInsert(['PersonID' => $personId], $filtered);
    }

    private function upsertOrDelete(string $table, int $personId, string $column, mixed $value): void
    {
        if ($value !== null && $value !== '') {
            DB::table($table)->updateOrInsert(
                ['PersonID' => $personId],
                [$column => $value]
            );

            return;
        }

        DB::table($table)->where('PersonID', $personId)->delete();
    }

    private function temporaryPassword(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $password = '';

        for ($i = 0; $i < 8; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }
}
