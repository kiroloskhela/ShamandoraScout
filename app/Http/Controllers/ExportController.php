<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportController extends Controller
{
    public function exportScoutsExcel(Request $request, $userId)
    {
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
                ppn.MotherMobileNumber,
            FROM PersonInformation pi
            LEFT JOIN PersonSanaMarhala psm     ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm            ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq            ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q                   ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonEntryQuestions peq  ON pi.PersonID = peq.PersonID
            LEFT JOIN PersonPhoneNumbers ppn    ON pi.PersonID = ppn.PersonID
            LEFT JOIN PersonGroup PG            ON PG.PersonID = pi.PersonID
            JOIN GroupQetaa gq                  ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2                ON pg2.GroupID = gq.GroupID
            WHERE q.QetaaID IN (
                SELECT gq2.QetaaID FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
            GROUP BY
                pi.PersonID, pi.ShamandoraCode, pi.FirstName, pi.SecondName,
                pi.ThirdName, pi.FourthName, q.QetaaName, pi.ScoutJoiningYear,
                sm.SanaMarhalaName, pi.RaqamQawmy, ppn.PersonPersonalMobileNumber,
                q.QetaaID, PG.PersonID, psm.SanaMarhalaID
            ORDER BY pi.ShamandoraCode ASC
        ", [$userId]);

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
            LEFT JOIN PeopleAllergies pa        ON pa.PersonID = pi.PersonID
            LEFT JOIN PeopleMedicalHistory pmh  ON pmh.PersonID = pi.PersonID
            JOIN GroupQetaa gq                  ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2                ON pg2.GroupID = gq.GroupID
            WHERE q.QetaaID IN (
                SELECT gq2.QetaaID FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
            GROUP BY
                pi.PersonID, pi.ShamandoraCode, pi.FirstName,
                pi.SecondName, pi.ThirdName, pi.FourthName, q.QetaaName
            HAVING
                FoodAllergies     IS NOT NULL OR
                MedicineAllergies IS NOT NULL OR
                Diseases          IS NOT NULL
            ORDER BY pi.ShamandoraCode ASC
        ", [$userId]);

        // ─── Sheet 3: Questions & Answers (dynamic pivot) ─────────────────────
        DB::statement("SET SESSION group_concat_max_len = 1000000");

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
            WHERE meq.QetaaID IN (
                SELECT gq2.QetaaID FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
        ", [$userId]);

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
                LEFT JOIN PersonEntryQuestions peq  ON pi.PersonID = peq.PersonID
                                                    AND peq.QuestionID IN (
                                                        SELECT QuestionID
                                                        FROM MarhalaEntryQuestions
                                                        WHERE QetaaID = q.QetaaID
                                                    )
                JOIN GroupQetaa gq                  ON gq.QetaaID = q.QetaaID
                JOIN PersonGroup pg2                ON pg2.GroupID = gq.GroupID
                WHERE q.QetaaID IN (
                    SELECT gq2.QetaaID FROM GroupQetaa gq2
                    WHERE gq2.GroupID IN (
                        SELECT pg3.GroupID FROM PersonGroup pg3
                        WHERE pg3.PersonID = ?
                    )
                )
                GROUP BY
                    pi.PersonID, pi.ShamandoraCode, pi.FirstName,
                    pi.SecondName, pi.ThirdName, pi.FourthName, q.QetaaName
                ORDER BY pi.ShamandoraCode ASC
            ";
            $sheet3Data = DB::select($dynamicQuery, [$userId]);
        }

        // ─── Build Excel ──────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();

        $this->fillSheet($spreadsheet->getActiveSheet()->setTitle('البيانات الشخصية'),   $sheet1Data);
        $this->fillSheet($spreadsheet->createSheet()->setTitle('الحساسية والتاريخ الطبي'), $sheet2Data);
        $this->fillSheet($spreadsheet->createSheet()->setTitle('الأسئلة والأجوبة'),       $sheet3Data);

        $spreadsheet->setActiveSheetIndex(0);

        // ─── Stream to browser ────────────────────────────────────────────────
        $filename = 'scouts_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─── Helper: fill a worksheet with data + styled header ──────────────────
    private function fillSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $data): void
    {
        if (empty($data)) {
            $sheet->setCellValue('A1', 'لا توجد بيانات');
            return;
        }

        $headers = array_keys((array) $data[0]);
        $col     = 1;

        // Header row
        foreach ($headers as $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($header);
            $cell->getStyle()->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $sheet->getColumnDimensionByColumn($col)->setWidth(22);
            $col++;
        }

        // Data rows
        $row = 2;
        foreach ($data as $record) {
            $col = 1;
            foreach ((array) $record as $value) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $cell->setValue($value ?? '');
                $cell->getStyle()->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                ]);
                $col++;
            }
            // Alternate row shading
            if ($row % 2 === 0) {
                $sheet->getStyleByColumnAndRow(1, $row, count($headers), $row)
                      ->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setRGB('F2F7FC');
            }
            $row++;
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // RTL direction for Arabic content
        $sheet->setRightToLeft(true);
    }
}