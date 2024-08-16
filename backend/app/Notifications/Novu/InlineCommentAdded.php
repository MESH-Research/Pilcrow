<?php
declare(strict_types=1);

namespace App\Notifications\Novu;

use Novu\Laravel\Facades\Novu;

class InlineCommentAdded
{
    /**
     * @param \InlineComment $inlineComment
     * @return array
     */
    public function __construct($inlineComment)
    {
        try {
            $response = Novu::triggerEvent([
                'name' => 'inline-comment-created',
                'payload' => [
                    'comment_id' => $inlineComment->id,
                    'submission_id' => $inlineComment->submission->id,
                    'submission_title' => $inlineComment->submission->title,
                    'user_label' => $inlineComment->createdBy->displayLabel,
                ],
                'to' => $this->getRecipients($inlineComment),
            ])->toArray();

            return $response;
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * @param \InlineComment $inlineComment
     * @return array
     */
    private function getRecipients($inlineComment)
    {
        $submitters = $inlineComment->submission->submitters;
        $coordinators = $inlineComment->submission->reviewCoordinators;
        return $submitters->merge($coordinators)->pluck('id')->map(function ($id) {
            return (string)$id;
        })->toArray();
    }
}
