<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Submission;
use App\Models\User;
use Tests\ApiTestCase;

class SubmissionMetaFormsTest extends ApiTestCase
{
    public function testCanViewSubmissionMetaForms()
    {
        Submission::factory()
            ->has(User::factory(), 'submitters')
            ->create([
                'status' => Submission::DRAFT,
            ]);
    }
}
