<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\TypeManipulator;

/**
 * Injects one `Boolean!` field per case of an ability enum onto the type it
 * decorates, so the GraphQL ability types stay locked to the enums and cannot
 * drift. A new ability case needs no schema edit.
 *
 * Field names use {@see Str::snake} of the enum CASE name — the exact derivation
 * the resolvers ({@see \App\Models\User::globalAbilities()},
 * {@see \App\Models\Submission::abilities()},
 * {@see \App\Models\Publication::abilities()}) use to key their snake_case map.
 * Both sides therefore read from the same source: the enum case name. The enum's
 * backing value (the Bouncer / legacy slug) is deliberately NOT used here, so
 * those identifiers remain free to follow their own conventions.
 */
class AbilityFieldsDirective extends BaseDirective implements TypeManipulator
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
Inject one `Boolean!` field per case of the given ability enum, named by
`Str::snake` of the case name — the same derivation the `abilities()` resolvers
use to key their map. Keeps the type locked to the enum so a new case needs no
schema edit.
"""
directive @abilityFields(
  """
  Fully-qualified ability enum class, e.g. "App\\Auth\\Abilities\\SubmissionAbility".
  """
  enum: String!
) on OBJECT
GRAPHQL;
        // phpcs:enable
    }

    /**
     * Append a `Boolean!` field per enum case to the decorated object type.
     *
     * @param \Nuwave\Lighthouse\Schema\AST\DocumentAST $documentAST
     * @param \GraphQL\Language\AST\TypeDefinitionNode&\GraphQL\Language\AST\Node $typeDefinition
     * @return void
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function manipulateTypeDefinition(DocumentAST &$documentAST, TypeDefinitionNode &$typeDefinition): void
    {
        if (! $typeDefinition instanceof ObjectTypeDefinitionNode) {
            throw new DefinitionException(
                '@abilityFields may only decorate an object type, used on ' . $typeDefinition->getName()->value . '.'
            );
        }

        $enum = $this->directiveArgValue('enum');
        if (! is_string($enum) || ! enum_exists($enum)) {
            throw new DefinitionException(
                "@abilityFields on {$typeDefinition->getName()->value} requires a valid `enum` class, got "
                . var_export($enum, true) . '.'
            );
        }

        $existing = [];
        foreach ($typeDefinition->fields as $field) {
            $existing[$field->name->value] = true;
        }

        foreach ($enum::cases() as $case) {
            $fieldName = Str::snake($case->name);
            if (isset($existing[$fieldName])) {
                continue;
            }
            $typeDefinition->fields[] = Parser::fieldDefinition("{$fieldName}: Boolean!");
        }
    }
}
