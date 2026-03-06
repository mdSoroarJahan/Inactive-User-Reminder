<?php

namespace App\Console\Commands;

use App\Jobs\SendInactiveUserReminder;
use App\Models\ReminderLog;
use App\Models\User;
use Illuminate\Console\Command;

class CheckInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch reminders to inactive users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) config('reminder.inactive_days', 7);

        $cutoff = now()->subDays($days);
        $sentCount = 0;

        User::query()
            ->where(function ($query) use ($cutoff) {
                $query
                    ->where('last_login_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query
                            ->whereNull('last_login_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->select(['id'])
            ->chunkById(500, function ($users) use (&$sentCount) {
                $userIds = $users->pluck('id')->all();

                $alreadySentUserIds = ReminderLog::query()
                    ->whereIn('user_id', $userIds)
                    ->whereDate('sent_at', today())
                    ->pluck('user_id')
                    ->all();

                $alreadySentLookup = array_fill_keys($alreadySentUserIds, true);

                foreach ($users as $user) {
                    if (! isset($alreadySentLookup[$user->id])) {
                        SendInactiveUserReminder::dispatch($user->id);
                        $sentCount++;
                    }
                }
            });

        $this->info("Dispatched reminders: {$sentCount}");

        return self::SUCCESS;
    }
}
