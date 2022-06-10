<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seed and create a publication with an administrator and editor.
     *
     * @param \App\Models\User $admin
     * @param \App\Models\User $editor
     * @return void
     */
    public function run(User $admin, User $editor)
    {
        Publication::factory()
        ->hasAttached($admin, [], 'publicationAdmins')
        ->hasAttached($editor, [], 'editors')
        ->create([
            'id' => 1,
            'name' => 'CCR Test Publication 1',
        ]);
    }
}
