<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PaginatorInterfaceTest extends TestCase
{
    /**
     * Verify that all *Paginator types implement the Paginator interface.
     *
     * @return void
     */
    public function test_all_paginator_types_implement_interface(): void
    {
        Artisan::call('lighthouse:print-schema');
        $schema = Artisan::output();

        $expectedTypes = [
            'UserPaginator',
            'PublicationPaginator',
            'SubmissionPaginator',
            'NotificationPaginator',
        ];

        foreach ($expectedTypes as $type) {
            $this->assertMatchesRegularExpression(
                "/type {$type} implements Paginator/",
                $schema,
                "{$type} should implement the Paginator interface."
            );
        }
    }
}
