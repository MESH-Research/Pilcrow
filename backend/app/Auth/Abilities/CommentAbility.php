<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

/**
 * Scoped abilities acting on a single {@see \App\Models\Contracts\Comment}
 * (inline or overall, top-level or reply).
 *
 * Comments carry no roles of their own, so these are resolved against the
 * comment's OWNING SUBMISSION by {@see \App\Auth\ScopedAbilityResolver}: the
 * user's submission roles supply the grant, and a predicate
 * ({@see \App\Auth\Grants\Predicates\OwnsCommentWhileReviewable}) ties it to
 * authorship and the submission still being reviewable. CREATION is a separate
 * concern, gated by {@see SubmissionAbility::Review} on the submission.
 *
 * Held (conditionally) by every comment-capable role via the Reviewer base
 * grant; the app-administrator role moderates unconditionally through the
 * resolver's short-circuit.
 *
 * Cases annotated {@see Exposed} are part of the public GraphQL contract: they
 * become values of the `CommentAbility` GraphQL enum and appear in the viewer's
 * granted-abilities array on a comment. Unannotated cases stay server-only.
 */
enum CommentAbility: string implements ScopedAbility
{
    /** Edit a comment's content (and, for a top-level inline comment, style criteria / range). */
    #[Exposed('Viewer may edit this comment — content and, for a top-level inline comment, style criteria and range.')]
    case Update = 'comment.update';

    /** Soft-delete a comment. */
    #[Exposed('Viewer may delete this comment.')]
    case Delete = 'comment.delete';
}
