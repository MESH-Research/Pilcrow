<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MetaQuestionType;
use App\Models\Publication;
use App\Models\StyleCriteria;
use App\Models\MetaQuestion;
use App\Models\MetaQuestionSet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seed and create a publication with an administrator and editor.
     *
     * @return void
     */
    public function run()
    {
        $this->callOnce(UserSeeder::class);

        Publication::factory()
            ->hasAttached(User::firstWhere('username', 'publicationAdministrator'), [], 'publicationAdmins')
            ->hasAttached(User::firstWhere('username', 'publicationEditor'), [], 'editors')
            ->has(
                MetaQuestionSet::factory()
                    ->state([
                        'name' => 'Basic Submission Information',
                        'caption' => 'Please provide the basic information about your submission.',
                    ])
                    ->has(
                        MetaQuestion::factory()
                            ->count(4)
                            ->state(new Sequence(
                                [
                                    'label' => 'What is your name?',
                                    'type' => MetaQuestionType::INPUT
                                ],
                                [
                                    'label' => 'What is your favorite color?',
                                    'type' => MetaQuestionType::SELECT,
                                    'options' => '{"options": ["Red", "Green", "Blue"]}'
                                ],
                                [
                                    'label' => 'Describe your submission.',
                                    'type' => MetaQuestionType::TEXTAREA
                                ],
                                [
                                    'label' => 'Are you sure you want to submit?',
                                    'type' => MetaQuestionType::CHECKBOX,
                                ]
                            ))
                    ),
            )
            ->has(
                MetaQuestionSet::factory()
                    ->state([
                        'name' => 'Additional Information',
                        'caption' => 'Please provide any additional information that may be relevant to your submission.',
                        'required' => true,
                    ])
            )
            ->create([
                'id' => 1,
                'name' => 'Pilcrow Test Publication 1',
                'is_accepting_submissions' => true,
            ]);
        Publication::factory()
            ->create([
                'name' => 'Pilcrow Test Publication Reject Submissions',
                'is_accepting_submissions' => false,
            ]);
        Publication::factory()
            ->count(50)
            ->has(
                StyleCriteria::factory()
                    ->count(4)
            )
            ->create();
    }
}
