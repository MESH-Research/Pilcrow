<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

use Attribute;

/**
 * Marks an ability enum case as part of the public GraphQL API contract.
 *
 * Cases are server-only by default; annotating one exposes it as a value of the
 * corresponding GraphQL ability enum (via the `@abilityEnum` schema directive)
 * and includes it in the granted-abilities arrays the `abilities` resolvers
 * return. The description is required — nothing can be exposed undocumented —
 * and flows into the schema as the enum value's description (introspection,
 * GraphiQL, codegen docs).
 *
 * An unexposed case fails safe: invisible to clients, skipped by the resolvers,
 * still fully usable server-side (policies, grants, Bouncer).
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class Exposed
{
    /**
     * @param string $description Required: becomes the GraphQL enum value's
     *   description in the public schema.
     */
    public function __construct(public readonly string $description)
    {
    }
}
