<?php
declare(strict_types=1);

namespace Tests;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

abstract class ApiTestCase extends TestCase
{
    use MakesGraphQLRequests;
}
