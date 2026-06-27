<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Abilities\GlobalAbility;
use App\GraphQL\Directives\AbilityFieldsDirective;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Printer;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Tests\TestCase;

/**
 * Coverage for the @abilityFields type manipulator, which injects one
 * `Boolean!` field per case of an ability enum onto the type it decorates so
 * the GraphQL ability types stay locked to the enums.
 */
class AbilityFieldsDirectiveTest extends TestCase
{
    /**
     * Build the AST, hydrate the directive from the decorated type, and run the
     * manipulator — returning the (mutated) type definition for assertions.
     */
    private function manipulate(string $sdl, string $typeName): TypeDefinitionNode
    {
        $ast = DocumentAST::fromSource($sdl);
        $typeDefinition = $ast->types[$typeName];

        /** @var \GraphQL\Language\AST\DirectiveNode $directiveNode */
        $directiveNode = collect($typeDefinition->directives)
            ->first(fn(DirectiveNode $node) => $node->name->value === 'abilityFields');

        $directive = new AbilityFieldsDirective();
        $directive->hydrate($directiveNode, $typeDefinition);
        $directive->manipulateTypeDefinition($ast, $typeDefinition);

        return $typeDefinition;
    }

    /**
     * The manipulator appends one `Boolean!` field per enum case, named by
     * Str::snake of the case name.
     *
     * @return void
     */
    public function test_injects_a_boolean_field_per_enum_case(): void
    {
        $type = $this->manipulate(<<<'GRAPHQL'
            type UserAbilities @abilityFields(enum: "App\\Auth\\Abilities\\GlobalAbility") {
              placeholder: Boolean
            }
            GRAPHQL, 'UserAbilities');

        $this->assertInstanceOf(ObjectTypeDefinitionNode::class, $type);

        $fields = [];
        foreach ($type->fields as $field) {
            $fields[$field->name->value] = Printer::doPrint($field->type);
        }

        foreach (GlobalAbility::cases() as $case) {
            $fieldName = Str::snake($case->name);
            $this->assertArrayHasKey($fieldName, $fields);
            $this->assertSame('Boolean!', $fields[$fieldName]);
        }
    }

    /**
     * A field already present on the type is left untouched — the manipulator
     * never duplicates it.
     *
     * @return void
     */
    public function test_skips_a_field_already_present(): void
    {
        $type = $this->manipulate(<<<'GRAPHQL'
            type UserAbilities @abilityFields(enum: "App\\Auth\\Abilities\\GlobalAbility") {
              publication_create: String
            }
            GRAPHQL, 'UserAbilities');

        $matches = collect($type->fields)
            ->filter(fn($field) => $field->name->value === 'publication_create');

        $this->assertCount(1, $matches);
        // The pre-existing declaration wins; it is not overwritten with Boolean!.
        $this->assertSame('String', Printer::doPrint($matches->first()->type));
    }

    /**
     * The manipulator rejects a non-object type — abilities only make sense on
     * an object.
     *
     * @return void
     */
    public function test_rejects_a_non_object_type(): void
    {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage('@abilityFields may only decorate an object type');

        $this->manipulate(<<<'GRAPHQL'
            enum NotAnObject @abilityFields(enum: "App\\Auth\\Abilities\\GlobalAbility") {
              FOO
            }
            GRAPHQL, 'NotAnObject');
    }

    /**
     * The manipulator rejects an `enum` argument that is not a real enum class.
     *
     * @return void
     */
    public function test_rejects_an_enum_argument_that_is_not_an_enum_class(): void
    {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage('requires a valid `enum` class');

        $this->manipulate(<<<'GRAPHQL'
            type UserAbilities @abilityFields(enum: "App\\Does\\Not\\Exist") {
              placeholder: Boolean
            }
            GRAPHQL, 'UserAbilities');
    }
}
