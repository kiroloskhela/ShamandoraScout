<?php

namespace App\Http\Controllers;

use App\Domain\Person\ServedPeopleExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    public function __construct(
        private readonly ServedPeopleExportService $export,
    ) {}

    public function form()
    {
        $user = Auth::user();

        return view('person.served-export', [
            'qetaas' => $this->export->allowedQetaas($user),
            'seasons' => DB::table('Season')
                ->orderByDesc('SeasonYear')
                ->orderBy('SeasonName')
                ->get(['SeasonID', 'SeasonName', 'SeasonYear']),
        ]);
    }

    public function download(Request $request)
    {
        set_time_limit(300);

        $data = $request->validate([
            'qetaa_id' => ['required', 'integer'],
            'season_id' => ['required', 'integer', 'exists:Season,SeasonID'],
        ]);

        $user = Auth::user();
        $qetaaId = (int) $data['qetaa_id'];
        $seasonId = (int) $data['season_id'];

        if (! $this->export->canExportQetaa($user, $qetaaId)) {
            abort(403);
        }

        $workbook = $this->export->build($qetaaId, $seasonId);

        Log::info('served_people.export', [
            'person_id' => (int) $user->PersonID,
            'qetaa_id' => $qetaaId,
            'season_id' => $seasonId,
            'people_count' => $workbook['people_count'],
        ]);

        $spreadsheet = new Spreadsheet;
        $first = true;
        foreach ($workbook['sheets'] as $sheet) {
            $worksheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $first = false;
            $this->fillSheet(
                $worksheet->setTitle($sheet['title']),
                $sheet['rows'],
                $sheet['photos'] ?? []
            );
        }
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'served_export_'.$qetaaId.'_'.$seasonId.'_'.now()->format('Y-m-d_H-i-s').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $data
     * @param  array<int, string>  $photos
     */
    private function fillSheet(Worksheet $sheet, array $data, array $photos = []): void
    {
        if ($data === []) {
            $sheet->setCellValue('A1', 'لا توجد بيانات');

            return;
        }

        $headers = array_map(fn ($header) => self::excelCell($header), array_keys($data[0]));
        $rows = [$headers];
        foreach ($data as $record) {
            $rows[] = array_map(
                static fn ($value) => self::excelCell($value),
                array_values($record)
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

        $this->embedPhotos($sheet, $headers, $photos);

        $sheet->freezePane('A2');
        $sheet->setRightToLeft(true);
    }

    /**
     * @param  list<string>  $headers
     * @param  array<int, string>  $photos
     */
    private function embedPhotos(Worksheet $sheet, array $headers, array $photos): void
    {
        if ($photos === []) {
            return;
        }

        $photoCol = array_search('Photo', $headers, true);
        if ($photoCol === false) {
            return;
        }

        $colLetter = Coordinate::stringFromColumnIndex($photoCol + 1);
        $sheet->getColumnDimension($colLetter)->setWidth(14);

        foreach ($photos as $rowIndex => $path) {
            if (! is_string($path) || $path === '' || ! is_file($path) || ! is_readable($path)) {
                continue;
            }
            if (filesize($path) > 5 * 1024 * 1024) {
                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                continue;
            }

            $excelRow = $rowIndex + 2;
            try {
                $drawing = new Drawing;
                $drawing->setName('Photo');
                $drawing->setPath($path);
                $drawing->setCoordinates($colLetter.$excelRow);
                $drawing->setHeight(54);
                $drawing->setOffsetX(4);
                $drawing->setOffsetY(4);
                $drawing->setWorksheet($sheet);
                $sheet->getRowDimension($excelRow)->setRowHeight(58);
            } catch (\Throwable) {
                continue;
            }
        }
    }

    private static function excelCell(mixed $value): string
    {
        $text = (string) ($value ?? '');
        if ($text !== '' && in_array($text[0], ['=', '+', '-', '@'], true)) {
            return "'".$text;
        }

        return $text;
    }
}
