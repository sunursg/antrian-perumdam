<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$events = \App\Models\QueueEvent::latest('id')->limit(5)->get();

echo json_encode($events->toArray(), JSON_PRETTY_PRINT);
