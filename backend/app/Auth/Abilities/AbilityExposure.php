<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

use Illuminate\Support\Str;
use ReflectionEnum;
use UnitEnum;

/**
 * Reads the {@see Exposed} attribute off ability enum cases: which cases are
 * part of the public GraphQL contract, their exposed names, and their required
 * descriptions. The single derivation both sides share — the `@abilityEnum`
 * schema directive builds the GraphQL enum values from it, and the `abilities`
 * resolvers emit granted exposed names through it — so the schema and the runtime
 * cannot drift.
 */
final class AbilityExposure
{
    /**
     * The exposed cases of an ability enum, keyed by exposed name, with their
     * descriptions.
     *
     * Pure over the enum class — attributes are code constants — so the
     * reflection is memoized for the process lifetime (same rationale as
     * {@see \App\Auth\Roles\ScopedRole::rolesGranting}): the abilities
     * resolvers call this per entity, and a list endpoint must not pay a
     * ReflectionEnum walk per row.
     *
     * @param class-string<\UnitEnum> $enum
     * @return array<string, array{case: \UnitEnum, description: string}>
     */
    public static function exposed(string $enum): array
    {
        static $cache = [];
        if (isset($cache[$enum])) {
            return $cache[$enum];
        }

        $reflection = new ReflectionEnum($enum);

        $exposed = [];
        foreach ($reflection->getCases() as $case) {
            $attributes = $case->getAttributes(Exposed::class);
            if ($attributes === []) {
                continue;
            }
            /** @var \App\Auth\Abilities\Exposed $attribute */
            $attribute = $attributes[0]->newInstance();
            $instance = $case->getValue();
            $exposed[self::exposedName($instance)] = [
                'case' => $instance,
                'description' => $attribute->description,
            ];
        }

        return $cache[$enum] = $exposed;
    }

    /**
     * The GraphQL enum value for a case: `Str::snake` of the case name,
     * matching this schema's lowercase enum-value convention (`UserRoles`,
     * `PublicationRole`) and the former ability field names.
     */
    public static function exposedName(UnitEnum $case): string
    {
        return Str::snake($case->name);
    }
}
