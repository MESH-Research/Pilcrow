<?php
declare(strict_types=1);

namespace App\Rules;

use App\Models\InlineComment;
use Exception;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class InlineCommentIdValidity implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Error message for validation error message
     *
     * @var string
     */
    private $error_message;

    /**
     * The parent of the inline comment thread
     *
     * @var \App\Models\InlineComment
     */
    private $parent;

    /**
     * The inline comment being replied to
     *
     * @var \App\Models\InlineComment
     */
    private $reply_to;

    /**
     * Supplied attribute value
     *
     * @var mixed
     */
    private $value;

    /**
     * Set the data under validation
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Determine if the validation rule passes
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->value = $value;

        $data = $this->data['input']['inline_comments']['create'][0];
        $parent_id_exists = array_key_exists('parent_id', $data);
        $reply_to_id_exists = array_key_exists('reply_to_id', $data);

        // If the attribute is unspecified, then the other must also be unspecified
        if (!$parent_id_exists && !$reply_to_id_exists) {
            return true;
        }
        if ($parent_id_exists && $reply_to_id_exists) {
            if (
                // If the attribute is null, then the other must also be null
                is_null($data['parent_id']) &&
                is_null($data['reply_to_id'])
            ) {
                return true;

            } elseif (
                // If the attribute has a value, then the other must also have a value
                !is_null($data['parent_id']) &&
                !is_null($data['reply_to_id'])
            ) {
                try {
                    $this->parent = InlineComment::where('id', $data['parent_id'])->firstOrFail();
                } catch (Exception $error) {
                    $this->error_message = $error->getMessage();
                    return false;
                }

                // Verify the validity of IDs
                $matches_submission = $this->checkIfParentBelongsToTheSameSubmission();
                $matches_parent = $this->checkIfReplyIsTheParentOrIsAReplyToTheParent();

                if ($matches_submission && $matches_parent) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * This checks if the parent of the comment being replied to belongs to the same submission
     *
     * @return bool
     */
    private function checkIfParentBelongsToTheSameSubmission()
    {
        if ($this->parent->submission_id === (int)$this->data['input']['id']) {
            return true;
        }
        $this->error_message = 'The parent does not belong to the same submission.';

        return false;
    }

    /**
     * This checks if the comment being replied to is the parent itself
     * or if the comment being replied to is a reply of the parent
     *
     * @return bool
     */
    private function checkIfReplyIsTheParentOrIsAReplyToTheParent()
    {
        $reply_to_id = (int)$this->data['input']['inline_comments']['create'][0]['reply_to_id'];
        if ($this->parent->id === $reply_to_id) {
            return true;
        }
        $reply_ids = $this->parent->replies()->pluck('id')->toArray();
        if (in_array($reply_to_id, $reply_ids)) {
            return true;
        }
        $this->error_message = 'The comment being replied to is not the parent ' .
                                'or is not a reply of the parent.';

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->error_message) {
            return $this->error_message;
        }

        return 'The value `' . json_encode($this->value) . '` for :attribute is invalid.';
    }
}
