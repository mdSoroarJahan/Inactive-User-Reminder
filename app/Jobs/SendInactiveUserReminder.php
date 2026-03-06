<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\ReminderLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendInactiveUserReminder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            Log::warning('Skipping reminder: user not found.', ['user_id' => $this->userId]);

            return;
        }

        Log::info('Reminder sent.', ['user_id' => $user->id]);

        ReminderLog::create([
            'user_id' => $user->id,
            'sent_at' => now(),
        ]);
    }
}
