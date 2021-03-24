<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    public function testPublicationsCanBeCreatedWithCustomNames()
    {
        $publication = Publication::factory()->create(['name' => 'Custom Name']);
        $this->assertEquals($publication->name, 'Custom Name');
    }
}
