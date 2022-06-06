<?php
declare(strict_types=1);

namespace App\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Str;

class ContentProvider extends Base
{
    /**
     * Generate content for a submission
     *
     * @param int $sections
     * @return string
     */
    public function content(int $sections = 3): string
    {
        $markdown = '';
        for ($i = 0; $i < $sections; $i++) {
            $markdown .= $this->contentSection(static::numberBetween(3, 5));
        }

        return Str::markdown($markdown);
    }

    /**
     * Generate a second of content
     *
     * @param int $paragraphs
     * @return string
     */
    protected function contentSection(int $paragraphs): string
    {
        $parts = [$this->contentSectionTitle()];
        for ($i = 0; $i < $paragraphs; $i++) {
            $parts[] = $this->contentParagraph();
        }

        return $this->arrayToString($parts);
    }

    /**
     * Generate a paragraph of content
     *
     * @return string
     */
    protected function contentParagraph(): string
    {
        return $this->generator->sentences(static::numberBetween(5, 8), true);
    }

    /**
     * Generate a section title
     *
     * @return string
     */
    protected function contentSectionTitle(): string
    {
        return '## ' . Str::title($this->generator->catchPhrase());
    }

    /**
     * Reducer for converting
     *
     * @param array $arr
     * @return string
     */
    protected function arrayToString(array $arr): string
    {
        return array_reduce($arr, function (string $acc, string $item) {
            return $acc . $item . "\n\n";
        }, '');
    }
}
