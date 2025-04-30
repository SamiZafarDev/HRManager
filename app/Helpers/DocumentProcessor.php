<?php

namespace App\Helpers;

// use Spatie\PdfToText\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class DocumentProcessor
{
    public static function extractText($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        try {
            switch ($extension) {
                case 'pdf':
                    return self::extractFromPdf($filePath);
                case 'docx':
                    return self::extractFromDocx($filePath);
                case 'txt':
                    return file_get_contents($filePath);
                default:
                    throw new \Exception("Unsupported file format: $extension");
            }
        } catch (\Exception $e) {
            // Log the error and skip the file
            Log::error("File extraction failed for file: {$filePath}. Error: " . $e->getMessage());
            throw new \Exception("File extraction failed: " . $e->getMessage());
        }
    }

    private static function extractFromDocx($filePath)
    {
        $text = '';

        try {
            // Open the DOCX file as a zip archive
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                // Extract document.xml (contains the main text)
                $xmlContent = $zip->getFromName('word/document.xml');
                $zip->close();

                if ($xmlContent) {
                    // Load the XML content and extract text
                    $xml = simplexml_load_string($xmlContent);
                    $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                    // Extract text from XML nodes
                    $textNodes = $xml->xpath('//w:t');
                    foreach ($textNodes as $node) {
                        $text .= (string) $node . " ";
                    }
                }
            }

            $text = trim($text);
        } catch (\Throwable $th) {
            $text = 'No content due to ' . $th->getMessage();
        }

        return empty($text) ? 'No content' : $text;
    }

    private static function extractFromPdf(string $filePath): string
    {
        try {
            // Check file size (e.g., 10MB limit)
            if (filesize($filePath) > 10 * 1024 * 1024) {
                throw new \Exception("File size exceeds the allowed limit.");
            }

            $parser = new Parser();
            // $pdf = $parser->parseFile($filePath);
            $fileContent = file_get_contents($filePath);
            $pdf = $parser->parseContent($fileContent);

            return $pdf->getText();
        } catch (\Exception $e) {
            throw new \RuntimeException("PDF extraction failed: " . $e->getMessage());
        }
    }
}
