<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NewUserEnrolment maps to `NewUsersInformation`, the denormalized
 * enrolment/document *staging* table (not the live PersonInformation
 * table). Rows here represent pending applicants awaiting approval into
 * PersonInformation/NewUsersInformationWaitinglist.
 *
 * Historically this table had no PRIMARY KEY at all (PersonID was a plain
 * NOT NULL column populated manually by app code). Package A
 * (2026_07_15_000002_package_a_new_users_keys) added a surrogate
 * AUTO_INCREMENT `id` column as the real PK plus UNIQUE(RaqamQawmy) and an
 * INDEX(QetaaID, SanaMarhalaID) for capacity counts. $primaryKey below is
 * `id`, not `PersonID` — PersonID remains a plain attribute here.
 */
class NewUserEnrolment extends Model
{
    protected $table = 'NewUsersInformation';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'ShamandoraCode',
        'FirstName',
        'SecondName',
        'ThirdName',
        'FourthName',
        'Gender',
        'DateOfBirth',
        'RaqamQawmy',
        'ScoutJoiningYear',
        'BloodTypeID',
        'FacebookProfileURL',
        'InstagramProfileURL',
        'PersonalEmail',
        'BuildingNumber',
        'FloorNumber',
        'AppartmentNumber',
        'MainStreetName',
        'SubStreetName',
        'ManteqaDistrictID',
        'NearestLandmark',
        'ManteqaID',
        'DistrictID',
        'SanaMarhalaID',
        'SpiritualFatherName',
        'SpiritualFatherChurchID',
        'SpiritualFatherChurchName',
        'Password',
        'IsApproved',
        'PersonPersonalMobileNumber',
        'FatherMobileNumber',
        'MotherMobileNumber',
        'HomePhoneNumber',
        'IsOPersonalPhoneNumberHavingWhatsapp',
        'SchoolName',
        'SchoolGraduationYear',
        'QetaaID',
        'QetaaName',
        'FacultyID',
        'UniversityID',
        'UniversityGraduationYear',
        'PersonalImagePath',
        'ScoutImagePath',
        'AllergyFood',
        'AllergyMedicine',
        'MedicalDiseases',
        'MedicalMedications',
        'HasEmergencyCase',
        'EmergencyDetails',
    ];

    protected $hidden = ['Password'];

    public function qetaa()
    {
        return $this->belongsTo(Qetaa::class, 'QetaaID', 'QetaaID');
    }

    public function sanaMarhala()
    {
        return $this->belongsTo(SanaMarhala::class, 'SanaMarhalaID', 'SanaMarhalaID');
    }
}
