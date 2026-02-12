<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\QueueEvent;
use App\Models\QueueTicket;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$latestEvent = QueueEvent::latest('id')->first();
$latestTicket = QueueTicket::latest('updated_at')->first();

echo "--- LATEST EVENT ---\n";
echo json_encode($latestEvent?->toArray(), JSON_PRETTY_PRINT);
echo "\n\n--- LATEST TICKET ---\n";
echo json_encode($latestTicket?->toArray(), JSON_PRETTY_PRINT);
