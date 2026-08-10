<?php

namespace App\Http\Controllers;

use App\Support\TableColumnFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    /** @var array<string, string> */
    private const FILTER_COLUMNS = [
        'SanaMarhalaName' => 'sm.SanaMarhalaName',
        'QetaaName' => 'q.QetaaName',
    ];

    public function exportScoutsExcel(Request $request)
    {
        // Production nginx often times out at 60s; keep room for large scoped exports.
        @set_time_limit(300);

        $userId = (int) Auth::id();
        $filters = TableColumnFilters::fromRequest($request, array_keys(self::FILTER_COLUMNS));
        $filterFrag = TableColumnFilters::sqlEquals($filters, self::FILTER_COLUMNS);
        $filterSql = $filterFrag['sql'] !== '' ? ' AND '.$filterFrag['sql'] : '';
        $filterBindings = $filterFrag['bindings'];
        $scopeSql = $this->orgScopeSql();

        // ─── Sheet 1: Personal Details ────────────────────────────────────────
        $sheet1Data = DB::select("
            SELECT
                pi.PersonID,
                pi.ShamandoraCode,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                pi.ScoutJoiningYear,
                sm.SanaMarhalaName,
                pi.RaqamQawmy,
                ppn.PersonPersonalMobileNumber,
                ppn.MotherMobileNumber
            FROM PersonInformation pi
            LEFT JOIN PersonSanaMarhala psm     ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm            ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq            ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q                   ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn    ON pi.PersonID = ppn.PersonID
            WHERE {$scopeSql}
            {$filterSql}
            GROUP BY
                pi.PersonID, pi.ShamandoraCode, pi.FirstName, pi.SecondName,
                pi.ThirdName, pi.FourthName, q.QetaaName, pi.ScoutJoiningYear,
                sm.SanaMarhalaName, pi.RaqamQawmy, ppn.PersonPersonalMobileNumber,
                ppn.MotherMobileNumber
            ORDER BY pi.ShamandoraCode ASC
        ", array_merge([$userId], $filterBindings));

        // ─── Sheet 2: Medical & Allergies ─────────────────────────────────────
        $sheet2Data = DB::select("
            SELECT
                pi.PersonID,
                pi.ShamandoraCode,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                GROUP_CONCAT(DISTINCT CASE WHEN pa.AllergyType = 'Food'     THEN pa.AllergyName END SEPARATOR ' | ') AS FoodAllergies,
                GROUP_CONCAT(DISTINCT CASE WHEN pa.AllergyType = 'Medicine' THEN pa.AllergyName END SEPARATOR ' | ') AS MedicineAllergies,
                GROUP_CONCAT(DISTINCT pmh.Disease          SEPARATOR ' | ') AS Diseases,
                GROUP_CONCAT(DISTINCT pmh.Medication       SEPARATOR ' | ') AS Medications,
                MAX(pmh.HasEmergencyCase)                                    AS HasEmergencyCase,
                GROUP_CONCAT(DISTINCT pmh.EmergencyDetails SEPARATOR ' | ') AS EmergencyDetails
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq            ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q                   ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonSanaMarhala psm     ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm            ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PeopleAllergies pa        ON pa.PersonID = pi.PersonID
            LEFT JOIN PeopleMedicalHistory pmh  ON pmh.PersonID = pi.PersonID
            WHERE {$scopeSql}
            {$filterSql}
            GROUP BY
                pi.PersonID, pi.ShamandoraCode, pi.FirstName,
                pi.SecondName, pi.ThirdName, pi.FourthName, q.QetaaName
            HAVING
                FoodAllergies     IS NOT NULL OR
                MedicineAllergies IS NOT NULL OR
                Diseases          IS NOT NULL
            ORDER BY pi.ShamandoraCode ASC
        ", array_merge([$userId], $filterBindings));

        // ─── Sheet 3: Questions & Answers (dynamic pivot) ─────────────────────
        DB::statement('SET SESSION group_concat_max_len = 1000000');

        $colsFilterSql = '';
        $colsBindings = [$userId];
        if (isset($filters['QetaaName'])) {
            $colsFilterSql = ' AND q.QetaaName = ?';
            $colsBindings[] = $filters['QetaaName'];
        }

        $colsResult = DB::selectOne("
            SELECT GROUP_CONCAT(
                DISTINCT CONCAT(
                    'MAX(CASE WHEN peq.QuestionID = ', meq.QuestionID,
                    ' THEN peq.Answer END) AS `',
                    REPLACE(meq.QuestionText, '`', ''), '`'
                )
                ORDER BY meq.QuestionID ASC
                SEPARATOR ', '
            ) AS cols
            FROM MarhalaEntryQuestions meq
            LEFT JOIN Qetaa q ON q.QetaaID = meq.QetaaID
            WHERE meq.QetaaID IN (
                SELECT gq2.QetaaID FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
            {$colsFilterSql}
        ", $colsBindings);

        $sheet3Data = [];
        if ($colsResult && $colsResult->cols) {
            $dynamicQuery = "
                SELECT
                    pi.PersonID,
                    pi.ShamandoraCode,
                    pi.FirstName,
                    pi.SecondName,
                    pi.ThirdName,
                    pi.FourthName,
                    q.QetaaName,
                    {$colsResult->cols}
                FROM PersonInformation pi
                LEFT JOIN PersonQetaa pq            ON pi.PersonID = pq.PersonID
                LEFT JOIN Qetaa q                   ON pq.QetaaID = q.QetaaID
                LEFT JOIN PersonSanaMarhala psm     ON pi.PersonID = psm.PersonID
                LEFT JOIN SanaMarhala sm            ON sm.SanaMarhalaID = psm.SanaMarhalaID
                LEFT JOIN PersonEntryQuestions peq  ON pi.PersonID = peq.PersonID
                                                    AND peq.QuestionID IN (
                                                        SELECT QuestionID
                                                        FROM MarhalaEntryQuestions
                                                        WHERE QetaaID = q.QetaaID
                                                    )
                WHERE {$scopeSql}
                {$filterSql}
                GROUP BY
                    pi.PersonID, pi.ShamandoraCode, pi.FirstName,
                    pi.SecondName, pi.ThirdName, pi.FourthName, q.QetaaName
                ORDER BY pi.ShamandoraCode ASC
            ";
            $sheet3Data = DB::select($dynamicQuery, array_merge([$userId], $filterBindings));
        }

        $spreadsheet = new Spreadsheet;

        $this->fillSheet($spreadsheet->getActiveSheet()->setTitle('البيانات الشخصية'), $sheet1Data);
        $this->fillSheet($spreadsheet->createSheet()->setTitle('الحساسية والتاريخ الطبي'), $sheet2Data);
        $this->fillSheet($spreadsheet->createSheet()->setTitle('الأسئلة والأجوبة'), $sheet3Data);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'scouts_export_'.now()->format('Y-m-d_H-i-s').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    private function orgScopeSql(): string
    {
        return 'q.QetaaID IN (
            SELECT gq2.QetaaID FROM GroupQetaa gq2
            WHERE gq2.GroupID IN (
                SELECT pg3.GroupID FROM PersonGroup pg3
                WHERE pg3.PersonID = ?
            )
        )';
    }

    private function fillSheet(Worksheet $sheet, array $data): void
    {
        if ($data === []) {
            $sheet->setCellValue('A1', 'لا توجد بيانات');

            return;
        }

        $headers = array_keys((array) $data[0]);
        $rows = [array_values($headers)];
        foreach ($data as $record) {
            $rows[] = array_map(
                static fn ($value) => $value ?? '',
                array_values((array) $record)
            );
        }

        $sheet->fromArray($rows, null, 'A1', true);

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = count($rows);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);
        }

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimensionByColumn($col)->setWidth(22);
        }

        $sheet->freezePane('A2');
        $sheet->setRightToLeft(true);
    }
}
