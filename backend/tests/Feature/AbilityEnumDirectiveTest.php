<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Abilities\AbilityExposure;
use App\Auth\Abilities\GlobalAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\GraphQL\Directives\AbilityEnumDirective;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Tests\TestCase;

/**
 * Coverage for the @abilityEnum type manipulator, which builds a GraphQL
 * ability enum's values from the Exposed cases of a PHP ability enum so the
 * public ability vocabulary stays locked to the single source of truth.
 */
class AbilityEnumDirectiveTest extends TestCase
{
    /**
     * Build the AST, hydrate the directive from the decorated enum, and run the
     * manipulator — returning the (mutated) type definition for assertions.
     */
    private function manipulate(string $sdl, string $typeName): TypeDefinitionNode
    {
        $ast = DocumentAST::fromSource($sdl);
        $typeDefinition = $ast->types[$typeName];

        /** @var \GraphQL\Language\AST\DirectiveNode $directiveNode */
        $directiveNode = collect($typeDefinition->directives)
            ->first(fn(DirectiveNode $node) => $node->name->value === 'abilityEnum');

        $directive = new AbilityEnumDirective();
        $directive->hydrate($directiveNode, $typeDefinition);
        $directive->manipulateTypeDefinition($ast, $typeDefinition);

        return $typeDefinition;
    }

    /**
     * The manipulator appends one enum value per EXPOSED case — named by the
     * shared exposed-name derivation, carrying the required description — and no
     * value for unexposed cases.
     *
     * @return void
     */
    public function test_builds_one_value_per_exposed_case(): void
    {
        $type = $this->manipulate(<<<'GRAPHQL'
            enum SubmissionAbility @abilityEnum(enum: "App\\Auth\\Abilities\\SubmissionAbility")
            GRAPHQL, 'SubmissionAbility');

        $this->assertInstanceOf(EnumTypeDefinitionNode::class, $type);

        $values = [];
        foreach ($type->values as $value) {
            $values[$value->name->value] = $value->description?->value;
        }

        $exposed = AbilityExposure::exposed(SubmissionAbility::class);
        $this->assertSame(array_keys($exposed), array_keys($values));
        foreach ($exposed as $exposedName => $exposure) {
            $this->assertSame($exposure['description'], $values[$exposedName]);
        }

        // The deprecated server-only bridge never reaches the schema.
        $this->assertArrayNotHasKey(
            AbilityExposure::exposedName(SubmissionAbility::LegacyUpdate),
            $values
        );
    }

    /**
     * The derived admin-area case is exposed like any other: it must be part of
     * the public vocabulary the resolver's derived union can emit.
     *
     * @return void
     */
    public function test_exposes_the_derived_admin_area_case(): void
    {
        $type = $this->manipulate(<<<'GRAPHQL'
            enum UserAbility @abilityEnum(enum: "App\\Auth\\Abilities\\GlobalAbility")
            GRAPHQL, 'UserAbility');

        $names = collect($type->values)->map(fn($value) => $value->name->value);

        $this->assertContains(AbilityExposure::exposedName(GlobalAbility::AdminArea), $names);
    }

    /**
     * A value already present on the enum is left untouched — the manipulator
     * never duplicates it.
     *
     * @return void
     */
    public function test_skips_a_value_already_present(): void
    {
        $type = $this->manipulate(<<<'GRAPHQL'
            enum PublicationAbility @abilityEnum(enum: "App\\Auth\\Abilities\\PublicationAbility") {
              view
            }
            GRAPHQL, 'PublicationAbility');

        $matches = collect($type->values)
            ->filter(fn($value) => $value->name->value === 'view');

        $this->assertCount(1, $matches);
    }

    /**
     * Descriptions are carried as AST string nodes, never spliced into SDL
     * source — so text that would break block-string lexing (trailing `"`,
     * trailing `\`, embedded `"""`) reaches the schema verbatim, and the
     * printed SDL (the schema the client codegen consumes) still round-trips
     * through the parser.
     *
     * @return void
     */
    public function test_descriptions_hostile_to_sdl_survive_verbatim(): void
    {
        $type = $this->manipulate(<<<'GRAPHQL'
            enum HostileAbility @abilityEnum(enum: "Tests\\Support\\SdlHostileAbility")
            GRAPHQL, 'HostileAbility');

        $descriptions = [];
        foreach ($type->values as $value) {
            $descriptions[$value->name->value] = $value->description?->value;
        }

        $expected = [
            'trailing_quote' => 'Viewer may act as "editor"',
            'trailing_backslash' => 'Path ends in C:\\',
            'embedded_triple_quote' => 'Contains a """ delimiter inline',
        ];
        $this->assertSame($expected, $descriptions);

        // Printing and re-parsing must not throw, and must preserve the text.
        $reparsed = Parser::parse(Printer::doPrint($type));
        /** @var \GraphQL\Language\AST\EnumTypeDefinitionNode $reparsedType */
        $reparsedType = $reparsed->definitions[0];
        foreach ($reparsedType->values as $value) {
            $this->assertSame($expected[$value->name->value], $value->description?->value);
        }
    }

    /**
     * The manipulator rejects a non-enum type.
     *
     * @return void
     */
    public function test_rejects_a_non_enum_type(): void
    {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage('@abilityEnum may only decorate an enum type');

        $this->manipulate(<<<'GRAPHQL'
            type NotAnEnum @abilityEnum(enum: "App\\Auth\\Abilities\\GlobalAbility") {
              placeholder: Boolean
            }
            GRAPHQL, 'NotAnEnum');
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
            enum UserAbility @abilityEnum(enum: "App\\Does\\Not\\Exist")
            GRAPHQL, 'UserAbility');
    }
}
