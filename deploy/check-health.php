<?php

/**
 * Post-deploy readiness check. Exit 0 when /health reports ok + database.
 * Intended to run on the VPS from the app root: php deploy/check-health.php
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/health', 'GET');
$response = $kernel->handle($request);
$body = $response->getContent();
$kernel->terminate($request, $response);

echo $body, PHP_EOL;

$data = json_decode($body, true);
$ok = is_array($data)
    && ($data['ok'] ?? false) === true
    && (($data['checks']['database'] ?? false) === true)
    && $response->getStatusCode() === 200;

if (! $ok) {
    fwrite(STDERR, 'Post-deploy health check failed'.PHP_EOL);
    exit(1);
}

exit(0);
