<?php
declare(strict_types=1);

namespace Tests\Feature;

use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SchemaSnapshotTest extends TestCase
{
    private const PRINT_OPTIONS = [
        'sortArguments' => true,
        'sortEnumValues' => true,
        'sortFields' => true,
        'sortInputFields' => true,
        'sortTypes' => true,
    ];

    /**
     * Normalize a schema string by parsing and re-printing in sorted order.
     *
     * @param string $schemaString
     * @return string
     */
    private function normalizeSchema(string $schemaString): string
    {
        $schema = BuildSchema::build(Parser::parse($schemaString));

        return SchemaPrinter::doPrint($schema, self::PRINT_OPTIONS);
    }

    public function test_compiled_schema_matches_committed_snapshot(): void
    {
        // Try the Lando path first (full repo mounted at /app),
        // fall back to the CI path (schema copied into backend build context).
        $clientPath = base_path('../client/src/graphql/schema.graphql');
        $ciPath = base_path('tests/stubs/schema.graphql');

        if (file_exists($clientPath)) {
            $snapshotPath = $clientPath;
        } elseif (file_exists($ciPath)) {
            $snapshotPath = $ciPath;
        } else {
            $this->fail(
                'No committed schema snapshot found. Expected at '
                . $clientPath . ' or ' . $ciPath
            );
        }

        $committedSchema = $this->normalizeSchema(file_get_contents($snapshotPath));

        Artisan::call('lighthouse:print-schema');
        $compiledSchema = $this->normalizeSchema(Artisan::output());

        $this->assertEquals(
            $committedSchema,
            $compiledSchema,
            "The committed client schema snapshot is out of sync with the backend schema.\n"
            . "Run 'yarn graphql:fetch-schema' from the client directory to update it."
        );
    }
}
