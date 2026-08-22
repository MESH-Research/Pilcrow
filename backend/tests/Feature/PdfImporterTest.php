<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Importers\PdfImporter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PdfImporterTest extends TestCase
{
    /**
     * Data provider for PDF testing
     *
     * @return array[]
     */
    public static function documentProvider(): array
    {
        return [
            ['test_structured.pdf', true],
            ['footnote_test.docx', false],
            ['test_docx_with_renamed_file_extension.pdf', false],
            ['test_pdf_with_incorrect_hex_signature.pdf', false],
        ];
    }

    /**
     * Test different documents against PDF detection code
     *
     * @param string $filename
     * @param bool $expected_result
     * @return void
     */
    #[DataProvider('documentProvider')]
    public function testDocFileIsNotDetectedAsPdf(string $filename, bool $expected_result): void
    {
        $stubPath = __DIR__ . '/../stubs/' . $filename;
        $isPdf = PdfImporter::isPdfFile($stubPath, $filename);
        $this->assertEquals($isPdf, $expected_result);
    }
}
