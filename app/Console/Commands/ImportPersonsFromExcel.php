<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPersonsFromExcel extends Command
{
    protected $signature = 'import:persons';
    protected $description = 'One-time import persons from full Excel file';

    public function handle()
    {
        $path = storage_path('app/import/full_persons_import.xlsx');

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Remove header row
        unset($rows[1]);

        foreach ($rows as $row) {
            DB::beginTransaction();

            try {
                // Check duplicate by RaqamQawmy
                $exists = DB::selectOne(
                    'SELECT EXISTS(SELECT 1 FROM PersonInformation WHERE RaqamQawmy = ?) AS ex',
                    [$row['G']]
                )->ex;

                if ($exists) {
                    $this->warn("Skipped duplicate RaqamQawmy: {$row['G']}");
                    DB::rollBack();
                    continue;
                }

                $last = DB::table('PersonInformation')->orderByDesc('PersonID')->first();
                $personID = $last ? $last->PersonID + 1 : 1;

                $code = 'SH-' . str_pad($personID, 5, '0', STR_PAD_LEFT);

                // ---- PersonInformation ----
                DB::table('PersonInformation')->insert([
                    'PersonID' => $personID,
                    'ShamandoraCode' => $code,
                    'FirstName' => $row['A'],
                    'SecondName' => $row['B'],
                    'ThirdName' => $row['C'],
                    'FourthName' => $row['D'],
                    'Gender' => $row['E'],
                    'DateOfBirth' => $row['F'],
                    'RaqamQawmy' => $row['G'],
                    'ScoutJoiningYear' => $row['H'],
                    'BloodTypeID' => $row['I'],
                    'FacebookProfileURL' => $row['J'],
                    'InstagramProfileURL' => $row['K'],
                    'PersonalEmail' => $row['L'],
                    'RequestPersonID' => $row['M'],
                ]);

                // ---- PersonPhoneNumbers ----
                DB::table('PersonPhoneNumbers')->insert([
                    'PersonID' => $personID,
                    'PersonPersonalMobileNumber' => $row['N'],
                    'FatherMobileNumber' => $row['O'],
                    'MotherMobileNumber' => $row['P'],
                    'HomePhoneNumber' => $row['Q'],
                    'IsOPersonalPhoneNumberHavingWhatsapp' => $row['R'],
                ]);

                // ---- PersonalPhysicalAddress ----
                DB::table('PersonalPhysicalAddress')->insert([
                    'PersonID' => $personID,
                    'BuildingNumber' => $row['S'],
                    'FloorNumber' => $row['T'],
                    'AppartmentNumber' => $row['U'],
                    'MainStreetName' => $row['V'],
                    'SubStreetName' => $row['W'],
                    'ManteqaID' => $row['X'],
                    'DistrictID' => $row['Y'],
                    'ManteqaDistrictID' => $row['Z'],
                    'NearestLandmark' => $row['AA'],
                    'Longitude' => $row['AB'],
                    'Latitude' => $row['AC'],
                ]);

                // ---- PersonLearningInformation ----
                DB::table('PersonLearningInformation')->insert([
                    'PersonID' => $personID,
                    'SchoolLearningSystemTypeID' => $row['AD'],
                    'SchoolName' => $row['AE'],
                    'SchoolGraduationYear' => $row['AF'],
                    'FacultyID' => $row['AG'],
                    'UniversityID' => $row['AH'],
                    'ActualFacultyGraduationYear' => $row['AI'],
                ]);

                // ---- PersonQetaa ----
                DB::table('PersonQetaa')->insert([
                    'PersonID' => $personID,
                    'QetaaID' => $row['AJ'],
                ]);

                // ---- PersonSanaMarhala ----
                DB::table('PersonSanaMarhala')->insert([
                    'PersonID' => $personID,
                    'SanaMarhalaID' => $row['AK'],
                ]);

                // ---- PersonSpiritualFatherInformation ----
                DB::table('PersonSpiritualFatherInformation')->insert([
                    'PersonID' => $personID,
                    'SpiritualFatherName' => $row['AL'],
                    'SpiritualFatherChurchID' => $row['AM'],
                    'SpiritualFatherChurchName' => $row['AN'],
                ]);

                DB::commit();
                $this->info("Imported PersonID {$personID}");

            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Error importing row with RaqamQawmy {$row['G']}: " . $e->getMessage());
            }
        }

        $this->info('Import finished');
    }
}