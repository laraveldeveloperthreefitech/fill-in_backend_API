<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleInterview;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpirePendingInterviews extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:expire-pending-interviews';

    /**
     * The console command description.
     */
    protected $description = 'Expire pending interviews after 30 minutes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $expireBeforeTime = Carbon::now()->subMinutes(30);


            $interviews = ScheduleInterview::where('interview_status', 1)
                ->where('created_at', '<=', $expireBeforeTime)
                ->get();

            if ($interviews->isEmpty()) {
                Log::info('No pending interviews found older than 30 minutes.');
                return Command::SUCCESS;
            }


            $interviewIds = $interviews->pluck('id')->toArray();


            ScheduleInterview::whereIn('id', $interviewIds)->update([
                'interview_status' => 3,
            ]);

            Log::info('Interview status updated to 3 for IDs: ' . implode(',', $interviewIds));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Expire pending interviews cron failed. Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
