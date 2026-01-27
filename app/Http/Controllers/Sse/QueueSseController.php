<?php

namespace App\Http\Controllers\Sse;

use App\Http\Controllers\Controller;
use App\Models\QueueEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QueueSseController extends Controller
{
    public function stream(Request $request): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($request) {
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            @ini_set('output_buffering', '0');

            while (ob_get_level() > 0) { ob_end_flush(); }
            ob_implicit_flush(true);

            $lastEventId = (int) ($request->header('Last-Event-ID') ?? $request->query('lastEventId', 0));
            $start = microtime(true);

            while (true) {
                if ((microtime(true) - $start) > 25) {
                    echo "event: ping\n";
                    echo "data: {}\n\n";
                    flush();
                    break;
                }

                $events = QueueEvent::query()
                    ->where('id', '>', $lastEventId)
                    ->orderBy('id')
                    ->limit(10)
                    ->get();

                if ($events->isNotEmpty()) {
                    foreach ($events as $ev) {
                        $lastEventId = $ev->id;
                        $payload = [
                            'type' => $ev->type,
                            'ticket_no' => $ev->ticket_no,
                            'service_code' => $ev->service_code,
                            'loket_code' => $ev->loket_code,
                            'status' => $ev->status,
                            'timestamp' => $ev->occurred_at?->toIso8601String(),
                            'payload' => $ev->payload,
                        ];

                        echo "id: {$ev->id}\n";
                        echo "event: queue_update\n";
                        echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                        flush();
                    }
                } else {
                    usleep(350000);
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
