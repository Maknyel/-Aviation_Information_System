<?php

namespace App\Console\Commands;

use App\Services\PendingRequestReminder;
use Illuminate\Console\Command;

class RemindPendingRequests extends Command
{
    protected $signature = 'requests:remind-pending';

    protected $description = 'Notify Admins about requests that have been pending for 2 or more days';

    public function handle()
    {
        $count = PendingRequestReminder::run();
        $this->info("Created {$count} pending-request reminder(s).");

        return 0;
    }
}
