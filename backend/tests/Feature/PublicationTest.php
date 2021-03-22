<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    public function testThatPublicationsCanBeAssignedToAndQueriedForAUser()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $user->publications()->attach([$publication->id]);
        $this->assertEquals($user->publications->first()->id, $publication->id);
    }

    public function testThatUsersCanBeAssignedToAndQueriedForAPublication()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $publication->users()->attach([$user->id]);
        $this->assertEquals($publication->users->first()->id, $user->id);
    }
}
