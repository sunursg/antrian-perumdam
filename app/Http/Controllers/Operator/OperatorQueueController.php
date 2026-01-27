<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\CallNextRequest;
use App\Models\Loket;
use App\Models\LoketAssignment;
use App\Models\QueueTicket;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OperatorQueueController extends Controller
{
    private function ensureAssigned(int $userId, Loket $loket): void
    {
        $assigned = LoketAssignment::query()
            ->where('user_id', $userId)
            ->where('loket_id', $loket->id)
            ->exists();

        if (!$assigned) {
            abort(403, 'Operator tidak ditugaskan ke loket ini.');
        }
    }

    public function myLokets(Request $request): JsonResponse
    {
        $user = $request->user();

        $lokets = Loket::query()
            ->whereHas('assignments', fn($q) => $q->where('user_id', $user->id))
            ->with('service:id,code,name')
            ->orderBy('code')
            ->get()
            ->map(fn($l) => [
                'code' => $l->code,
                'name' => $l->name,
                'service' => ['code' => $l->service->code, 'name' => $l->service->name],
            ]);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $lokets,
            'errors' => null,
        ]);
    }

    public function callNext(CallNextRequest $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();

        if (method_exists($user, 'can') && !$user->can('queue.call-next')) {
            abort(403, 'Tidak punya izin call-next.');
        }

        $loket = Loket::query()->where('code', $request->string('loket_code'))->firstOrFail();
        $this->ensureAssigned($user->id, $loket);

        $ticket = $queue->callNext($loket, $user->id);

        if (!$ticket) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada antrian menunggu.',
                'data' => null,
                'errors' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Memanggil nomor berikutnya.',
            'data' => [
                'ticket_no' => $ticket->ticket_no,
                'status' => $ticket->status,
                'loket' => ['code' => $loket->code, 'name' => $loket->name],
                'service' => ['code' => $ticket->service->code, 'name' => $ticket->service->name],
                'called_at' => $ticket->called_at?->toIso8601String(),
            ],
            'errors' => null,
        ]);
    }

    public function recall(Request $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();
        if (method_exists($user, 'can') && !$user->can('queue.recall')) {
            abort(403, 'Tidak punya izin recall.');
        }

        $ticket = QueueTicket::query()
            ->where('ticket_no', $request->string('ticket_no'))
            ->firstOrFail();

        if ($ticket->loket_id) {
            $loket = $ticket->loket()->first();
            if ($loket) $this->ensureAssigned($user->id, $loket);
        }

        try {
            $ticket = $queue->recall($ticket);
            return response()->json([
                'success' => true,
                'message' => 'Memanggil ulang.',
                'data' => [
                    'ticket_no' => $ticket->ticket_no,
                    'status' => $ticket->status,
                    'loket_code' => $ticket->loket?->code,
                    'called_at' => $ticket->called_at?->toIso8601String(),
                ],
                'errors' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal recall.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function skip(Request $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();
        if (method_exists($user, 'can') && !$user->can('queue.skip')) {
            abort(403, 'Tidak punya izin skip.');
        }

        $ticket = QueueTicket::query()
            ->where('ticket_no', $request->string('ticket_no'))
            ->firstOrFail();

        if ($ticket->loket_id) {
            $loket = $ticket->loket()->first();
            if ($loket) $this->ensureAssigned($user->id, $loket);
        }

        try {
            $ticket = $queue->markNoShow($ticket);
            return response()->json([
                'success' => true,
                'message' => 'Ditandai no-show.',
                'data' => [
                    'ticket_no' => $ticket->ticket_no,
                    'status' => $ticket->status,
                ],
                'errors' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal skip.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function serve(Request $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();
        if (method_exists($user, 'can') && !$user->can('queue.serve')) {
            abort(403, 'Tidak punya izin serve.');
        }

        $ticket = QueueTicket::query()
            ->where('ticket_no', $request->string('ticket_no'))
            ->firstOrFail();

        if ($ticket->loket_id) {
            $loket = $ticket->loket()->first();
            if ($loket) $this->ensureAssigned($user->id, $loket);
        }

        try {
            $ticket = $queue->serve($ticket);
            return response()->json([
                'success' => true,
                'message' => 'Layanan selesai.',
                'data' => [
                    'ticket_no' => $ticket->ticket_no,
                    'status' => $ticket->status,
                    'served_at' => $ticket->served_at?->toIso8601String(),
                ],
                'errors' => null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
