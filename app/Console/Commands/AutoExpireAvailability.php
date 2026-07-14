<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FillinShiftResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoExpireAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-expire-availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark pending availability as not-available after shift end time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            Log::info('========== AutoExpireAvailability Started ==========');

            $responses = FillinShiftResponse::with('shift')
                ->where('response', 'pending')
                ->get();

            if ($responses->isEmpty()) {
                Log::info('No pending responses found.');
                return self::SUCCESS;
            }

            foreach ($responses as $response) {

                if (!$response->shift) {
                    Log::warning("Shift not found for Response ID {$response->id}");
                    continue;
                }

                $shift = $response->shift;

                // Create shift end datetime
                $shiftEnd = Carbon::parse($shift->shift_date)
                    ->setTimeFromTimeString($shift->end_time);

                Log::info([
                    'response_id' => $response->id,
                    'shift_id'    => $shift->id,
                    'shift_end'   => $shiftEnd->toDateTimeString(),
                    'current'     => now()->toDateTimeString(),
                ]);

                if (now()->gte($shiftEnd)) {

                    $response->update([
                        'response'     => 'not-available',
                        'responded_at' => now(),
                    ]);

                    Log::info("Response ID {$response->id} updated successfully.");
                }
            }

            Log::info('========== AutoExpireAvailability Completed ==========');

            return self::SUCCESS;

        } catch (\Throwable $e) {

            Log::error('AutoExpireAvailability Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return self::FAILURE;
        }
    }
}