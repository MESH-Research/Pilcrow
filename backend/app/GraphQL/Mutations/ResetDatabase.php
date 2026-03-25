<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class ResetDatabase
{
    /**
     * Reset the database and run seeders.
     *
     * @param mixed  $_  unused root value
     * @param array<string, mixed>  $_args  unused arguments
     * @return string artisan output
     */
    public function __invoke($_, array $_args): string
    {
        abort_unless(
            App::environment(['local', 'testing']),
            403,
            'Database reset is not available in this environment.'
        );

        Artisan::call('migrate:fresh', ['--seed' => true]);

        return Artisan::output();
    }
}
