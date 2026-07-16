<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'GroupTable' => 'GroupID',
    'PersonRole' => 'PersonRoleID',
    'MarhalaEntryQuestions' => 'QuestionID',
    'EgazetBetakatTaqaddom' => 'EgazetBetakatTaqaddomID',
    'BloodType' => 'BloodTypeID',
    'Locations' => 'LocationID',
    'Place' => 'PlaceID',
    'Inventory' => 'InventoryID',
    'CurriculaCategory' => 'CurriculaCategoryID',
    'Roles' => 'RoleID',
    'Faculty' => 'FacultyID',
    'Qetaa' => 'QetaaID',
    'GroupType' => 'GroupTypeID',
    'EventType' => 'EventTypeID',
    'University' => 'UniversityID',
    'Districts' => 'DistrictID',
    'Manteqa' => 'ManteqaID',
    'RotbaInformation' => 'RotbaID',
    'Marhala' => 'MarhalaID',
    'SanaMarhala' => 'SanaMarhalaID',
];

try {
    foreach ($tables as $table => $pk) {
        if (! Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "MISSING {$table}\n";
            continue;
        }
        $row = Illuminate\Support\Facades\DB::selectOne(
            'SELECT COLUMN_NAME, COLUMN_KEY, EXTRA, IS_NULLABLE, COLUMN_TYPE, COLUMN_DEFAULT
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $pk]
        );
        if (! $row) {
            echo "NOCOL {$table}.{$pk}\n";
            continue;
        }
        $ai = str_contains(strtolower((string) $row->EXTRA), 'auto_increment') ? 'AI' : 'NO_AI';
        echo "{$ai}\t{$table}.{$pk}\t{$row->COLUMN_TYPE}\tKEY={$row->COLUMN_KEY}\tNULL={$row->IS_NULLABLE}\tEXTRA={$row->EXTRA}\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'DB_ERR: '.$e->getMessage().PHP_EOL);
    exit(1);
}
