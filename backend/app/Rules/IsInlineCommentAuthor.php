<?php
declare(strict_types=1);

namespace App\Rules;

use App\Models\InlineComment;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class IsInlineCommentAuthor implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

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
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $comment_id = $this->data['input']['inline_comments']['update'][0]['id'];
        $inline_comment = InlineComment::where('id', $comment_id)->firstOrFail();
        if ($inline_comment->created_by !== Auth::user()->id) {
            $fail('You may not modify the comments of others.');
        }
    }
}
