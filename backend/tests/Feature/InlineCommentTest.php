<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class InlineCommentTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;
}
