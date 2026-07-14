<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseNotificationService;
use App\Models\ScheduleInterview;
use Carbon\Carbon;
use App\Helpers\TimezoneHelper;
use Illuminate\Support\Facades\Log;


class ReminderInterview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminder-interview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send interview reminders to candidates and recruiters 15 minutes before scheduled time.';

    protected $fcm;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct(); // ✅ Required
        $this->fcm = new FirebaseNotificationService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

         // \Log::info('🔐 Schedule run before loop');

        // Get time 15 minutes ago
     

        // Fetch interviews scheduled for today at the target time
        $data = ScheduleInterview::with('candidate', 'job', 'clinic.recruiter')
                ->whereDate('date', Carbon::today())
                ->get();

        if ($data->isEmpty()) {
            // \Log::info('🔐  No interviews found for reminder. ');
           
            return;
        }

        foreach ($data as $row) {
             if ($row->timezone && $row->timezone != config('app.timezone')) {
                $validatetimezone = TimezoneHelper::sanitize($row->timezone); 
                config(['app.timezone' => $validatetimezone]);
                date_default_timezone_set($validatetimezone);
            } 
            try {
                $targetTime = Carbon::now()->addMinutes(15)->format('H:i');
                 // Log::info("Reminders sent for Interview ID: {$targetTime}");
                if($row->time == $targetTime){
                    // Notify candidate
                    $this->fcm->notifyCandidates(
                        $row->candidate_id,
                        'Hi ' . $row->candidate->name . ', reminder: Your interview is scheduled at ' . $row->time,
                        'Interview Reminder',
                        '', '', 'interview', $row->id, ''
                    );

                    // Notify recruiter
                    $this->fcm->notifyRecruiters(
                        $row->clinic->recruiter_id,
                        'Hi ' . $row->clinic->recruiter->name . ', reminder: You have an interview at ' . $row->time,
                        'Interview Reminder',
                        '', '', 'interview', $row->id, ''
                    );

                    \Log::info("Reminders sent for Interview ID: {$row->id}");
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send reminder for Interview ID: {$row->id}. Error: " . $e->getMessage());
            }
        }
    }
}
