<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AvatarReport;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Delete retained private snapshots of reported avatars once they pass their
 * purge_after deadline (set when a report is resolved by removal). The report
 * record itself is kept — only the evidence image is purged.
 */
class PurgeAvatarReportSnapshots extends Command
{
    protected $signature = 'avatar-reports:purge-snapshots';

    protected $description = 'Purge retained reported-avatar snapshots past their retention window';

    /**
     * Delete retained snapshots whose purge_after deadline has passed and
     * clear the deadline. The report rows themselves are kept.
     *
     * @return int
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $purged = 0;

        AvatarReport::query()
            ->whereNotNull('purge_after')
            ->where('purge_after', '<=', $now)
            ->chunkById(100, function ($reports) use (&$purged) {
                foreach ($reports as $report) {
                    if ($report->getSnapshotMedia() === null) {
                        // Nothing left to purge; clear the deadline so we stop
                        // re-examining this report.
                        $report->forceFill(['purge_after' => null])->save();
                        continue;
                    }

                    $report->clearMediaCollection(AvatarReport::SNAPSHOT_COLLECTION);
                    $report->forceFill(['purge_after' => null])->save();
                    $purged++;
                }
            });

        $this->info("Purged {$purged} reported-avatar snapshot(s).");

        return self::SUCCESS;
    }
}
