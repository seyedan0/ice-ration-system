<?php

namespace App\Console\Commands;

use App\Models\Citizen;
use App\Models\DailyTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDailyTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:daily-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unclaimed tickets from previous days and generate today\'s ration tickets for every active citizen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = today();
        $expiredCount = 0;
        $generatedCount = 0;

        DB::transaction(function () use ($today, &$expiredCount, &$generatedCount) {
            // Step 1: Expire yesterday's (and any older) unclaimed tickets. No rollover.
            $expiredCount = DailyTicket::query()
                ->where('status', DailyTicket::STATUS_PENDING)
                ->where('ticket_date', '<', $today)
                ->update(['status' => DailyTicket::STATUS_EXPIRED]);

            // Step 2: Generate today's tickets for every active citizen (skip if already exists).
            Citizen::query()
                ->active()
                ->chunkById(500, function ($citizens) use ($today, &$generatedCount) {
                    $inserts = $citizens->map(fn (Citizen $c) => [
                        'citizen_id' => $c->id,
                        'station_id' => $c->preferred_station_id,
                        'ticket_date' => $today->toDateString(),
                        'allocated_blocks' => $c->daily_ration,
                        'status' => DailyTicket::STATUS_PENDING,
                        'created_at' => now(),
                    ])->all();

                    $generatedCount += DailyTicket::query()->insertOrIgnore($inserts);
                });
        });

        $this->info("Daily tickets reset complete for {$today->toDateString()}.");
        $this->line("  Expired (unclaimed from previous days): {$expiredCount}");
        $this->line("  Generated (today's fresh tickets): {$generatedCount}");

        return self::SUCCESS;
    }
}
