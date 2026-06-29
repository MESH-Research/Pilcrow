<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AvatarReport;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a couple of pending avatar reports so the admin moderation
 * queue has something to exercise in local development. Runs after
 * AvatarSeeder so there are users with avatars to report against.
 */
class AvatarReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $reported = User::whereHas('media', fn($q) => $q->where('collection_name', 'avatar'))
            ->orderBy('id')
            ->take(2)
            ->get();

        if ($reported->count() < 2) {
            $this->command?->warn('Not enough seeded avatars to file sample reports; skipping.');

            return;
        }

        $reporter = User::orderBy('id')->whereNotIn('id', $reported->pluck('id'))->first();
        if ($reporter === null) {
            $this->command?->warn('No spare user to act as reporter; skipping.');

            return;
        }

        AvatarReport::create([
            'user_id' => $reported[0]->id,
            'reporter_user_id' => $reporter->id,
            'reason' => 'Sample report — the avatar looks inappropriate for this context.',
        ]);

        AvatarReport::create([
            'user_id' => $reported[1]->id,
            'reporter_user_id' => $reporter->id,
            'reason' => null,
        ]);
    }
}
