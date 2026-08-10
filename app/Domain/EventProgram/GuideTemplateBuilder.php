<?php

namespace App\Domain\EventProgram;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GuideTemplateBuilder
{
    public function absoluteXlsxPath(): string
    {
        $rel = (string) config('event_program.guide_xlsx', 'templates/event_program_guide.xlsx');
        $path = storage_path('app/'.$rel);
        if (! is_file($path)) {
            $this->writeGuideXlsx($path);
        }

        return $path;
    }

    public function ensureGuideExists(): string
    {
        return $this->absoluteXlsxPath();
    }

    public function writeGuideXlsx(string $path): void
    {
        $spreadsheet = new Spreadsheet;

        // Guide
        $guide = $spreadsheet->getActiveSheet();
        $guide->setTitle('Guide');
        $lines = [
            ['اقرأني / Guide'],
            ['1) املأ Meta: SeasonEventID + عنوان البرنامج + عدد الأيام'],
            ['2) عدّل Day 1 (وأضف Day 2..N بنفس الأعمدة)'],
            ['صف 1 = مدى الوقت، صف 2 = اسم الفقرة، من صف 3 = القادة والمهام'],
            ['الأعمدة: PersonID | ShamandoraCode | القائد | الفريق | ثم فقرات الوقت'],
            ['3) املأ Resources: kind | day_number | slot_label | title | url'],
            ['4) ارفع الملف من لوحة البرنامج أو الصق رابط Google Sheets'],
            ['مثال مصغّر موجود في Day 1 و Resources'],
        ];
        foreach ($lines as $i => $row) {
            $guide->fromArray($row, null, 'A'.($i + 1));
        }

        // Meta
        $meta = $spreadsheet->createSheet();
        $meta->setTitle('Meta');
        $meta->fromArray([
            ['SeasonEventID', ''],
            ['title', 'Ready Steady Go Camp'],
            ['day_count', '1'],
            ['intro_template', "أهلاً يا {title} {name}\n\nده برنامجك لليوم {day} في {event_name}\n"],
            ['outro_template', "\nشكراً على تعبك، وصلّي من أجل الخدمة ❤️"],
        ], null, 'A1');

        // Program overview
        $program = $spreadsheet->createSheet();
        $program->setTitle('Program');
        $program->fromArray([
            ['06:00', 'تجمع'],
            ['06:30', 'تحرك'],
            ['12:00', 'العاب'],
        ], null, 'A1');

        // Day 1 sample
        $day1 = $spreadsheet->createSheet();
        $day1->setTitle('Day 1');
        $day1->fromArray([
            ['', '', '', '', 'من 06:00 الي 06:30', 'من 06:30 الي 08:00', 'من 12:00 الي 13:00'],
            ['PersonID', 'ShamandoraCode', 'القائد', 'الفريق', 'التجمع', 'التحرك', 'العاب'],
            ['', 'SH-00001', 'قائد تجريبي ١', 'زهرات', 'التجمع', 'التحرك', 'العاب'],
            ['', 'SH-00002', 'قائد تجريبي ٢', 'أشبال', 'التجمع', 'ميديا', 'تحضير العاب'],
        ], null, 'A1');

        // Resources sample
        $res = $spreadsheet->createSheet();
        $res->setTitle('Resources');
        $res->fromArray([
            ['kind', 'day_number', 'slot_label', 'title', 'url'],
            ['game', '1', 'العاب', 'Catch the flag', 'https://example.com/game1'],
            ['lecture', '1', 'توصيل هدف', 'nutrition', 'https://example.com/lecture1'],
        ], null, 'A1');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        (new Xlsx($spreadsheet))->save($path);
    }

    public function downloadResponse(): StreamedResponse
    {
        $path = $this->ensureGuideExists();

        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, 'event_program_guide.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Write CSV pack next to xlsx for operators who prefer CSV.
     */
    public function writeCsvPack(string $dir): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $files = [
            'Meta.csv' => "key,value\nSeasonEventID,\ntitle,Ready Steady Go Camp\nday_count,1\n",
            'Day 1.csv' => "PersonID,ShamandoraCode,القائد,الفريق,من 06:00 الي 06:30,من 06:30 الي 08:00,من 12:00 الي 13:00\n,,,,"
                ."التجمع,التحرك,العاب\n,SH-00001,قائد تجريبي ١,زهرات,التجمع,التحرك,العاب\n",
            'Resources.csv' => "kind,day_number,slot_label,title,url\ngame,1,العاب,Catch the flag,https://example.com/game1\n",
            'Guide.csv' => "note\nFill Meta then Day sheets then Resources then upload\n",
        ];

        // Fix Day 1 CSV - need proper two header rows. Parser expects row0=times, row1=labels.
        $files['Day 1.csv'] = implode("\n", [
            ',,,,"من 06:00 الي 06:30","من 06:30 الي 08:00","من 12:00 الي 13:00"',
            'PersonID,ShamandoraCode,القائد,الفريق,التجمع,التحرك,العاب',
            ',SH-00001,قائد تجريبي ١,زهرات,التجمع,التحرك,العاب',
            ',SH-00002,قائد تجريبي ٢,أشبال,التجمع,ميديا,تحضير العاب',
            '',
        ]);

        foreach ($files as $name => $content) {
            file_put_contents($dir.'/'.$name, $content);
        }
    }
}
