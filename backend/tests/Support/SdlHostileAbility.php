<?php
declare(strict_types=1);

namespace Tests\Support;

use App\Auth\Abilities\Exposed;

/**
 * Fixture: descriptions containing every SDL block-string hazard — a trailing
 * double quote, a trailing backslash, and an embedded triple-quote delimiter.
 * Consumed by {@see \Tests\Feature\AbilityEnumDirectiveTest} to prove the
 * abilityEnum directive carries descriptions as AST nodes, immune to SDL
 * lexing edge cases.
 */
enum SdlHostileAbility
{
    #[Exposed('Viewer may act as "editor"')]
    case TrailingQuote;

    #[Exposed('Path ends in C:\\')]
    case TrailingBackslash;

    #[Exposed('Contains a """ delimiter inline')]
    case EmbeddedTripleQuote;
}
