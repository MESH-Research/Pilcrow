<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SubmissionCommentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testInlineCommentsAreNotRetrievedForASubmissionThatHasNone()
    {
        return true;
    }

    public function testOverallCommentsAreNotRetrievedForASubmissionThatHasNone()
    {
        return true;
    }

    public function testInlineCommentsCanBeRetrievedBySubmission()
    {
        return true;
    }

    public function testOverallCommentsCanBeRetrievedBySubmission()
    {
        return true;
    }

    public function testInlineCommentsCanBeRetrievedOnTheGraphqlEndpoint()
    {
        return true;
    }

    public function testOverallCommentsCanBeRetrievedOnTheGraphqlEndpoint()
    {
        return true;
    }

    public function testInlineCommentsCanBeCreatedOnTheGraphqlEndpoint()
    {
        return true;
    }

    public function testOverallCommentsCanBeCreatedOnTheGraphqlEndpoint()
    {
        return true;
    }
}
