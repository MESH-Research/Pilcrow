<?php
declare(strict_types=1);

namespace Tests;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Act as a new user with application administrator role
     *
     * @return \App\Models\User New user with adminsitrator role
     */
    public function beAppAdmin(): User
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);

        return $user;
    }

    /**
     * Act as a submitter of a submission
     *
     * @return \App\Models\User
     */
    public function beSubmitter(): User
    {
        /** @var User $submitter */
        $submitter = User::factory()->create();
        $this->actingAs($submitter);

        Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($submitter, [], 'submitters')
            ->create(['title' => 'Test submission with a submitter']);

        return $submitter;
    }

    /**
     * Act as a reviewer of a submission
     *
     * @return \App\Models\User
     */
    public function beReviewer(): User
    {
        /** @var User $reviewer */
        $reviewer = User::factory()->create();
        $this->actingAs($reviewer);

        Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['title' => 'Test submission with a reviewer']);

        return $reviewer;
    }

    /**
     * Act as a review coordinator of a submission
     *
     * @return \App\Models\User
     */
    public function beReviewCoordinator(): User
    {
        /** @var User $review_coordinator */
        $review_coordinator = User::factory()->create();
        $this->actingAs($review_coordinator);

        Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($review_coordinator, [], 'reviewCoordinators')
            ->create(['title' => 'Test submission with a review coordinator']);

        return $review_coordinator;
    }

    /**
     * Act as an editor of a publication with a submission
     *
     * @return \App\Models\User
     */
    public function beEditor(): User
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);
        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();
        Submission::factory()
            ->for($publication)
            ->create(['title' => 'Test submission with an editor']);

        return $editor;
    }

    /**
     * Act as a publication administrator of a publication with a submission
     *
     * @return \App\Models\User
     */
    public function bePubAdmin(): User
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $publication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->create();
        Submission::factory()
            ->for($publication)
            ->create(['title' => 'Test submission with a publication administrator']);

        return $admin;
    }

    /**
     * Remove the trace key from error arrays.
     *
     * Useful for debugging as the trace key overwhelms var_dump ouput of a response json.
     *
     * @param array $json
     * @return array
     */
    public function unsetJsonTrace($json): array
    {
        if (empty($json['errors'])) {
            return $json;
        }

        foreach ($json['errors'] as $index => $error) {
            unset($json['errors'][$index]['extensions']['trace']);
        }

        return $json;
    }
}
