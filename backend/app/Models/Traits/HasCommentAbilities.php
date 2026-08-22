<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Auth\Abilities\AbilityExposure;
use App\Auth\Abilities\CommentAbility;
use App\Auth\ScopedAbilityResolver;
use Illuminate\Support\Facades\Auth;

/**
 * The client-facing `abilities` resolver shared by both comment models
 * ({@see \App\Models\InlineComment}, {@see \App\Models\OverallComment} —
 * replies are same-table rows, so the four GraphQL comment types are covered).
 * One trait because a comment's authorization is uniform across the two models
 * ({@see \App\Models\Contracts\Comment}).
 */
trait HasCommentAbilities
{
    /**
     * The authenticated viewer's GRANTED abilities on this comment, as the
     * exposed names of {@see CommentAbility} cases.
     *
     * Resolved through {@see ScopedAbilityResolver} — the same engine
     * {@see \App\Policies\CommentPolicy} uses — against this comment's owning
     * submission, with the authorship/reviewable predicate evaluated against
     * the comment itself. So these client-facing values can never drift from
     * real authorization. Only {@see \App\Auth\Abilities\Exposed} cases are
     * evaluated. Guests get an empty array.
     *
     * UI hints only: the server still enforces every mutation with @can.
     *
     * @return array<int, string>
     */
    public function abilities(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user === null) {
            return [];
        }
        $resolver = app(ScopedAbilityResolver::class);

        $granted = [];
        foreach (AbilityExposure::exposed(CommentAbility::class) as $exposedName => $exposure) {
            /** @var \App\Auth\Abilities\CommentAbility $ability */
            $ability = $exposure['case'];
            if ($resolver->allows($user, $ability, $this)) {
                $granted[] = $exposedName;
            }
        }

        return $granted;
    }
}
