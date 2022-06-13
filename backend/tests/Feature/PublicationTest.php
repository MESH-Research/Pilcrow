<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\PublicationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function testNoDuplicateNames()
    {
        $publication = Publication::factory()->create(['name' => 'Custom Name']);
        $this->assertEquals($publication->name, 'Custom Name');

        $this->expectException(ValidationException::class);
        Publication::factory()->create(['name' => 'Custom Name']);
    }

    /**
     * @return array
     */
    public function publicationContentMutationProvider(): array
    {
        return [
            [
                [
                    'name' => 'Test Publication with Whitespace',
                    'home_page_content' => 'Amet animi quaerat eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                    'new_submission_content' => 'Voluptatem nam quidem perspiciatis. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.'
                ],
                [
                    'createPublication' => [
                        'name' => 'Test Publication with Whitespace',
                        'home_page_content' => 'Amet animi quaerat eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                        'new_submission_content' => 'Voluptatem nam quidem perspiciatis. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.'
                    ],
                ],
                ''
            ],
        ];
    }

    /**
     * @dataProvider publicationContentMutationProvider
     * @return void
     */
    public function testContentCreation(mixed $publication_data, mixed $expected_data, string $message): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication ($publication_name: String, $home_page_content: String, $new_submission_content: String) {
                createPublication(publication:{name: $publication_name home_page_content: $home_page_content new_submission_content: $new_submission_content}) {
                    name,
                    home_page_content,
                    new_submission_content,
                }
            }',
            [ 
              'publication_name' => $publication_data["name"], 
              'home_page_content' => $publication_data["home_page_content"],
              'new_submission_content' => $publication_data["new_submission_content"],
            ],
        );
        $json = $response->json();
        print_r($json);
        print_r($publication_data);
        $this->assertSame($json['data'] ?? null, $expected_data, $message);
    }
}
