<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Modules\Saas\Models\Landlord\DemoRequest;

class PruneDemoRequests extends Command
{
    protected $signature = 'saas:prune-demo-requests {--dry-run : Count expired requests without deleting them}';

    protected $description = 'Delete SaaS demo requests after their configured retention period';

    public function handle(): int
    {
        $query = DemoRequest::query()->where('purge_after', '<=', now());
        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} expired demo request(s) would be deleted.");

            return self::SUCCESS;
        }

        $query->delete();
        $this->info("Deleted {$count} expired demo request(s).");

        return self::SUCCESS;
    }
}
