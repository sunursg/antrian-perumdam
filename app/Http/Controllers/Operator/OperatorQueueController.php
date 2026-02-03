<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Enums\TicketStatus;
use App\Http\Requests\Operator\CallNextRequest;
use App\Http\Requests\Operator\TicketActionRequest;
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
        $user = auth()->user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('SUPER_ADMIN')) {
            return;
        }

        $assigned = LoketAssignment::query()
            ->where('user_id', $userId)
            ->where('loket_id', $loket->id)
            ->exists();

        if (!$assigned) {
            abort(403, 'Operator tidak ditugaskan ke loket ini.');
        }
    }

    private function ensurePermission($user, string $ability, string $message): void
    {
        if (!$user) {
            abort(401, 'Belum login.');
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('SUPER_ADMIN')) {
            return;
        }

        if (method_exists($user, 'can') && $user->can($ability)) {
            return;
        }

        abort(403, $message);
    }

    private function findLoketByCode(string $code): Loket
    {
        return Loket::query()->where('code', $code)->firstOrFail();
    }

    private function currentTicketForLoket(Loket $loket): ?QueueTicket
    {
        return QueueTicket::query()
            ->where('date_key', now()->format('Y-m-d'))
            ->where('loket_id', $loket->id)
            ->where('status', TicketStatus::DIPANGGIL->value)
            ->latest('called_at')
            ->first();
    }

    private function ensureTicketAssignment($user, QueueTicket $ticket): void
    {
        if (!$ticket->loket_id) {
            return;
        }

        $loket = $ticket->loket()->first();
        if ($loket) {
            $this->ensureAssigned($user->id, $loket);
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

    public function current(Request $request, string $loketCode): JsonResponse
    {
        $user = $request->user();
        $loket = $this->findLoketByCode($loketCode);
        $this->ensureAssigned($user->id, $loket);

        $ticket = $this->currentTicketForLoket($loket);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'ticket_no' => $ticket?->ticket_no,
                'status' => $ticket?->status,
                'called_at' => $ticket?->called_at?->toIso8601String(),
            ],
            'errors' => null,
        ]);
    }

    public function callNextForLoket(Request $request, QueueService $queue, string $loketCode): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.call-next', 'Tidak punya izin call-next.');

        $loket = $this->findLoketByCode($loketCode);
        $this->ensureAssigned($user->id, $loket);

        $ticket = $queue->callNext($loket, $user, $request->ip());

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

    public function recallForLoket(Request $request, QueueService $queue, string $loketCode): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.recall', 'Tidak punya izin recall.');

        $loket = $this->findLoketByCode($loketCode);
        $this->ensureAssigned($user->id, $loket);

        $ticket = $this->currentTicketForLoket($loket);
        if (!$ticket) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada tiket yang sedang dipanggil.',
                'data' => null,
                'errors' => null,
            ]);
        }

        try {
            $ticket = $queue->recall($ticket, $user, $request->ip());
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

    public function skipForLoket(Request $request, QueueService $queue, string $loketCode): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.skip', 'Tidak punya izin skip.');

        $loket = $this->findLoketByCode($loketCode);
        $this->ensureAssigned($user->id, $loket);

        $ticket = $this->currentTicketForLoket($loket);
        if (!$ticket) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada tiket yang sedang dipanggil.',
                'data' => null,
                'errors' => null,
            ]);
        }

        try {
            $ticket = $queue->markNoShow($ticket, $user, $request->ip());
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

    public function serveForLoket(Request $request, QueueService $queue, string $loketCode): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.serve', 'Tidak punya izin serve.');

        $loket = $this->findLoketByCode($loketCode);
        $this->ensureAssigned($user->id, $loket);

        $ticket = $this->currentTicketForLoket($loket);
        if (!$ticket) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada tiket yang sedang dipanggil.',
                'data' => null,
                'errors' => null,
            ]);
        }

        try {
            $ticket = $queue->serve($ticket, $user, $request->ip());
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

    public function callNext(CallNextRequest $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();

        $this->ensurePermission($user, 'queue.call-next', 'Tidak punya izin call-next.');

        $loket = $this->findLoketByCode($request->string('loket_code')->toString());
        $this->ensureAssigned($user->id, $loket);

        $ticket = $queue->callNext($loket, $user, $request->ip());

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

    public function recall(TicketActionRequest $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.recall', 'Tidak punya izin recall.');

        $ticket = QueueTicket::query()
            ->where('ticket_no', $request->string('ticket_no')->toString())
            ->firstOrFail();

        $this->ensureTicketAssignment($user, $ticket);

        try {
            $ticket = $queue->recall($ticket, $user, $request->ip());
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

    public function skip(TicketActionRequest $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.skip', 'Tidak punya izin skip.');

        $ticket = QueueTicket::query()
            ->where('ticket_no', $request->string('ticket_no')->toString())
            ->firstOrFail();

        $this->ensureTicketAssignment($user, $ticket);

        try {
            $ticket = $queue->markNoShow($ticket, $user, $request->ip());
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

    public function serve(TicketActionRequest $request, QueueService $queue): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.serve', 'Tidak punya izin serve.');

        $ticket = QueueTicket::query()
            ->where('ticket_no', $request->string('ticket_no')->toString())
            ->firstOrFail();

        $this->ensureTicketAssignment($user, $ticket);

        try {
            $ticket = $queue->serve($ticket, $user, $request->ip());
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

    public function recallById(Request $request, QueueService $queue, QueueTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.recall', 'Tidak punya izin recall.');
        $this->ensureTicketAssignment($user, $ticket);

        try {
            $ticket = $queue->recall($ticket, $user, $request->ip());
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

    public function skipById(Request $request, QueueService $queue, QueueTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.skip', 'Tidak punya izin skip.');
        $this->ensureTicketAssignment($user, $ticket);

        try {
            $ticket = $queue->markNoShow($ticket, $user, $request->ip());
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

    public function serveById(Request $request, QueueService $queue, QueueTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $this->ensurePermission($user, 'queue.serve', 'Tidak punya izin serve.');
        $this->ensureTicketAssignment($user, $ticket);

        try {
            $ticket = $queue->serve($ticket, $user, $request->ip());
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
