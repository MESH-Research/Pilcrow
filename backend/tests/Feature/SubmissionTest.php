<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testThatSubmissionsHaveAOneToManyRelationshipWithPublications()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #1',
        ]);
        $submissions = Submission::factory()->count(20)->create();
        $this->assertEquals($submissions->first()->publication->id, 1);
        $this->assertEquals($submissions->last()->publication->id, 1);
        $this->assertEquals($publication->submissions->count(), 20);
    }
}
