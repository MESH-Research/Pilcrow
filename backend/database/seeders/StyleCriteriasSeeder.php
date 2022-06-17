<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StyleCriteria;
use Illuminate\Database\Seeder;

class StyleCriteriasSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $style_criterias = [
            [
                'name' => 'Accessibility',
                'description' => '<ul><li>Can the author replace or explain any technical terms?</li><li>How does ' .
                                 'the piece consider multiple accessibility needs, such as accessing and interacting ' .
                                 'with text, audio, video, and other media?</li><li>Are people of all genders, races,' .
                                 'classes, religions, abilities, sexual orientations and other groups treated ' .
                                 'equitably in the piece? If so, how?</li></ul>',
                'icon' => 'accessibility',
            ],
            [
                'name' => 'Relevance',
                'description' => '<ul><li>Which specific audience(s) or communities does the author acknowledge, ' .
                                 'consider, and engage with in their work?</li><li>What organizations and individuals' .
                                 'are engaged in public initiatives associated with the questions or issues addressed' .
                                 'by the submission?</li><li>Why is this specific issue of interest to this specific ' .
                                 'at the time of composition or publication?</li></ul>',
                'icon' => 'close_fullscreen',
            ],
            [
                'name' => 'Coherence',
                'description' => '<ul><li>Does the author identify claims that support their argument?</li><li>How ' .
                                 'does the author explain how the claims are related to each other and the larger ' .
                                 'argument?</li><li>Does the author provide compelling evidence in support of their ' .
                                 'claims?</li><li>For more creative works, how does the author convey their intended ' .
                                 'message to readers, listeners, and/or reviewers?</li></ul>',
                'icon' => 'psychology',
            ],
            [
                'name' => 'Scholarly Dialogue',
                'description' => '<ul><li>How does the author demonstrate their awareness of existing discussions of ' .
                                 'their topic?</li><li>How has the author cited the work of others in their project?' .
                                 '</li><li>How do the author’s citations represent members of the community concerned' .
                                 ' with the issue at hand?</li></ul>',
                'icon' => 'question_answer',
            ],
        ];

        foreach ($style_criterias as $criteria) {
            StyleCriteria::factory()
                ->create([
                    'name' => $criteria['name'],
                    'publication_id' => 1,
                    'description' => $criteria['description'],
                    'icon' => $criteria['icon'],
                ]);
        }
    }
}
