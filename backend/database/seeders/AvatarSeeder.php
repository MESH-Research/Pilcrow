<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Throwable;

/**
 * Seeds a realistic-looking avatar onto a subset of users so the UI has
 * something non-identicon to poke at in local development.
 *
 * Avatars are fetched from pravatar.cc (CC-licensed portrait images,
 * deterministic per `?u=` seed). Requires outbound HTTPS from the
 * appserver container; the seeder logs + continues on fetch errors so
 * `php artisan migrate:fresh --seed` is never blocked by network issues.
 */
class AvatarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::orderBy('id')
            ->take(8)
            ->get()
            ->each(function (User $user): void {
                $url = 'https://i.pravatar.cc/512?u=' . urlencode($user->email);
                try {
                    $user->addMediaFromUrl($url)
                        ->usingFileName('avatar.jpg')
                        ->toMediaCollection(User::AVATAR_COLLECTION);
                } catch (FileCannotBeAdded | Throwable $e) {
                    $this->command?->warn(
                        "Skipping avatar for {$user->email}: {$e->getMessage()}"
                    );
                }
            });
    }
}
