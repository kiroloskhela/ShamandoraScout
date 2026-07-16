<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Domain-oriented route partials live in routes/web/. This file preserves
| the original registration order by requiring those partials sequentially.
|
*/

require __DIR__.'/web/auth.php';
require __DIR__.'/web/liveform.php';
require __DIR__.'/web/admin.php';
require __DIR__.'/web/person.php';
require __DIR__.'/web/finance.php';
require __DIR__.'/web/inventory.php';
require __DIR__.'/web/medicine.php';
require __DIR__.'/web/misc.php';
