<?php
declare(strict_types=1);

namespace App\Importers;

class PdfImporter
{
    /**
     * Determine if imported file is a PDF
     *
     * @param string $temporary_path
     * @param string|null $original_name
     * @return bool
     */
    public static function isPdfFile(string $temporary_path, ?string $original_name = null): bool
    {
        if (!is_readable($temporary_path) || filesize($temporary_path) === 0) {
            return false;
        }

        if (!PdfImporter::hasPdfHexSignature($temporary_path)) {
            return false;
        }

        if (!PdfImporter::hasPdfMimeType($temporary_path)) {
            return false;
        }

        if (PdfImporter::getFileExtension($original_name) !== 'pdf') {
            return false;
        }

        return true;
    }

    /**
     * Check if imported file has a PDF MIME type
     *
     * @param string $temporary_path
     * @return bool
     */
    private static function hasPdfMimeType($temporary_path)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $temporary_path);
                if ($mime === 'application/pdf') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if imported file begins with a PDF Hex signature
     *
     * @param string $temporary_path
     * @return bool
     */
    private static function hasPdfHexSignature(string $temporary_path)
    {
        $fp = fopen($temporary_path, 'rb');
        if ($fp) {
            $header = fread($fp, 5);
            fclose($fp);
            if ($header === '%PDF-') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get imported file's extension
     *
     * @param string $file_name
     * @return string
     */
    private static function getFileExtension(string $file_name): string
    {
        if ($file_name) {
            return strtolower(pathinfo($file_name, PATHINFO_EXTENSION) ?: '');
        }

        return '';
    }
}
