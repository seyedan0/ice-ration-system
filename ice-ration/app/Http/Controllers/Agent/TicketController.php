<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\DailyTicket;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class TicketController extends Controller
{
    public function show(Request $request)
    {
        return view('agent.validate');
    }

    /**
     * POST /agent/tickets/validate
     * Looks up a citizen by national_id, mobile, or qr_code and returns
     * today's ticket status.
     */
    public function validateIdentifier(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:100'],
        ]);

        $citizen = Citizen::findByIdentifier(trim($data['identifier']));

        if (! $citizen || ! $citizen->is_active) {
            return response()->json([
                'success' => true,
                'data' => ['status' => 'not_found'],
                'message' => 'Citizen not registered or inactive.',
            ]);
        }

        $ticket = $citizen->todayTicket();

        // Self-heal: generate today's ticket on the fly if the citizen was
        // registered/activated after the nightly reset already ran.
        if (! $ticket) {
            $ticket = DailyTicket::create([
                'citizen_id' => $citizen->id,
                'station_id' => $citizen->preferred_station_id,
                'ticket_date' => today(),
                'allocated_blocks' => $citizen->daily_ration,
                'status' => DailyTicket::STATUS_PENDING,
            ]);
        }

        $status = match ($ticket->status) {
            DailyTicket::STATUS_PENDING => 'approved',
            DailyTicket::STATUS_CLAIMED => 'claimed',
            default => 'expired',
        };

        return response()->json([
            'success' => true,
            'data' => [
                'citizen_name' => $citizen->full_name,
                'status' => $status,
                'allocated_blocks' => $ticket->allocated_blocks,
                'claimed_at' => $ticket->claimed_at?->toIso8601String(),
                'ticket_id' => $ticket->id,
                'ticket_station_id' => $ticket->station_id,
            ],
            'message' => match ($status) {
                'approved' => 'Approved for pickup.',
                'claimed' => 'Ration already claimed today.',
                default => 'Ticket is not valid for pickup.',
            },
        ]);
    }

    /**
     * POST /agent/tickets/{ticket}/claim
     * Atomically marks the ticket claimed and deducts the station stock.
     */
    public function claim(Request $request, DailyTicket $ticket): JsonResponse
    {
        $agent = $request->user();

        if (! $agent->station_id || $ticket->station_id !== $agent->station_id) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'This ticket belongs to a different station.',
            ], 403);
        }

        try {
            $result = DB::transaction(function () use ($ticket, $agent) {
                $lockedTicket = DailyTicket::query()->lockForUpdate()->findOrFail($ticket->id);

                if (! $lockedTicket->isPending()) {
                    throw new RuntimeException('ALREADY_CLAIMED');
                }

                $station = Station::query()->lockForUpdate()->findOrFail($lockedTicket->station_id);

                $lockedTicket->update([
                    'status' => DailyTicket::STATUS_CLAIMED,
                    'claimed_at' => now(),
                    'claimed_by_agent_id' => $agent->id,
                ]);

                $station->deductStock($lockedTicket->allocated_blocks, $agent->id, $lockedTicket->id);

                return [$lockedTicket->fresh(), $station->fresh()];
            });
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'ALREADY_CLAIMED') {
                $fresh = $ticket->fresh();

                return response()->json([
                    'success' => false,
                    'data' => [
                        'status' => 'claimed',
                        'claimed_at' => $fresh->claimed_at?->toIso8601String(),
                    ],
                    'message' => 'Already claimed today at ' . $fresh->claimed_at?->format('H:i') . '.',
                ], 409);
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Insufficient station stock to fulfill this claim.',
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Insufficient station stock to fulfill this claim.',
            ], 422);
        }

        [$claimedTicket, $station] = $result;

        return response()->json([
            'success' => true,
            'data' => [
                'ticket_id' => $claimedTicket->id,
                'status' => 'claimed',
                'claimed_at' => $claimedTicket->claimed_at?->toIso8601String(),
                'station_stock_after' => $station->current_stock,
            ],
            'message' => 'Delivery confirmed successfully.',
        ]);
    }
}
