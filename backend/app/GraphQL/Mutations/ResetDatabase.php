<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class ResetDatabase
{
    public function __invoke($_, array $args): string
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
