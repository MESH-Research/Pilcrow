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
        $style_criterias = collect([
            [
                'name' => 'Accessibility',
                'description' => 'Connects with the public at large and resonates with specific, publicly engaged
                                  individuals and organizations. This usually requires unpacking technical terms,
                                  linking to source and related materials, providing transcripts for audio and video,
                                  and providing alt-text for images.',
                'icon' => 'accessibility',
            ],
            [
                'name' => 'Relevance',
                'description' => 'Timely and responsive to an issue that concerns a specific public community.',
                'icon' => 'close_fullscreen',
            ],
            [
                'name' => 'Coherence',
                'description' => 'Compelling and well-ordered according to the genre of the piece.',
                'icon' => 'psychology',
            ],
            [
                'name' => 'Scholarly Dialogue',
                'description' => 'Cites and considers related discussions either within or outside of the academy,
                                  whether encountered in peer-reviewed literature or other media such as blogs,
                                  magazines, podcasts, galleries, or listservs.',
                'icon' => 'question_answer',
            ],
        ]);
        $style_criterias->map(function ($criteria) {
            StyleCriteria::factory()
                ->create([
                    'name' => $criteria['name'],
                    'publication_id' => 1,
                    'description' => $criteria['description'],
                    'icon' => $criteria['icon'],
                ]);
        });
    }
}
