<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\Auth\Abilities\AbilityExposure;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\TypeManipulator;

/**
 * Builds a GraphQL ability enum's values from the {@see \App\Auth\Abilities\Exposed}
 * cases of a PHP ability enum, so the public ability vocabulary is generated from
 * the single source of truth and cannot drift. A new exposed case needs no schema
 * edit; an unannotated case never reaches the schema.
 *
 * Value names use {@see AbilityExposure::exposedName} (`Str::snake` of the case
 * name) — the same derivation the `abilities()` resolvers emit — and each value
 * carries the case's required `Exposed` description into introspection.
 */
class AbilityEnumDirective extends BaseDirective implements TypeManipulator
{
    /**
     * The directive's SDL definition.
     *
     * @return string
     */
    public static function definition(): string
    {
        // phpcs:disable
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Build this enum's values from the `Exposed` cases of the given PHP ability enum,
named by `Str::snake` of the case name — the same derivation the `abilities()`
resolvers emit. Keeps the public ability vocabulary locked to the PHP enum.
"""
directive @abilityEnum(
  """
  Fully-qualified ability enum class, e.g. "App\\Auth\\Abilities\\SubmissionAbility".
  """
  enum: String!
) on ENUM
GRAPHQL;
        // phpcs:enable
    }

    /**
     * Append one enum value per exposed PHP enum case.
     *
     * @param \Nuwave\Lighthouse\Schema\AST\DocumentAST $documentAST
     * @param \GraphQL\Language\AST\TypeDefinitionNode&\GraphQL\Language\AST\Node $typeDefinition
     * @return void
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function manipulateTypeDefinition(DocumentAST &$documentAST, TypeDefinitionNode &$typeDefinition): void
    {
        if (! $typeDefinition instanceof EnumTypeDefinitionNode) {
            throw new DefinitionException(
                '@abilityEnum may only decorate an enum type, used on ' . $typeDefinition->getName()->value . '.'
            );
        }

        $enum = $this->directiveArgValue('enum');
        if (! is_string($enum) || ! enum_exists($enum)) {
            throw new DefinitionException(
                "@abilityEnum on {$typeDefinition->getName()->value} requires a valid `enum` class, got "
                . var_export($enum, true) . '.'
            );
        }

        $exposed = AbilityExposure::exposed($enum);
        if ($exposed === []) {
            throw new DefinitionException(
                "@abilityEnum on {$typeDefinition->getName()->value}: {$enum} exposes no cases."
            );
        }

        $existing = [];
        foreach ($typeDefinition->values as $value) {
            $existing[$value->name->value] = true;
        }

        foreach ($exposed as $exposedName => $exposure) {
            if (isset($existing[$exposedName])) {
                continue;
            }
            // Built as AST nodes, not spliced into SDL and re-parsed: a
            // StringValueNode carries the description verbatim, so no
            // character in it can break block-string lexing.
            $typeDefinition->values[] = new EnumValueDefinitionNode([
                'name' => new NameNode(['value' => $exposedName]),
                'description' => new StringValueNode([
                    'value' => $exposure['description'],
                    'block' => true,
                ]),
                'directives' => new NodeList([]),
            ]);
        }
    }
}
