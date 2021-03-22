<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    public function testPublicationsCanBeAssignedToAndQueriedForAUser()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $user->publications()->attach($publication->id);
        $this->assertEquals($user->publications->first()->id, $publication->id);
        $this->assertEquals($user->publications->count(), 1);
    }

    public function testUsersCanBeAssignedToAndQueriedForAPublication()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $publication->users()->attach([$user->id]);
        $this->assertEquals($publication->users->first()->id, $user->id);
        $this->assertEquals($publication->users->count(), 1);
    }

    public function testUsersCanBeUnAssignedToAndQueriedForAPublication()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $publication->users()->attach([$user->id]);
        $publication->users()->detach([$user->id]);
        $this->assertEmpty($publication->users->toArray());
        $this->assertEquals($publication->users->count(), 0);
    }

    public function testPublicationsCanBeUnAssignedToAndQueriedForAUser()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $user->publications()->attach([$publication->id]);
        $user->publications()->detach([$publication->id]);
        $this->assertEmpty($user->publications->toArray());
        $this->assertEquals($user->publications->count(), 0);
    }
}
