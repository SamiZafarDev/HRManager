<?php

namespace App\Helpers;

use Spatie\PdfToText\Pdf;
use Exception;

class DocumentProcessor
{
    public static function extractText($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'pdf':
                return Pdf::getText($filePath);
            case 'docx':
                return self::extractFromDocx($filePath);
            case 'txt':
                return file_get_contents($filePath);
            default:
                throw new Exception("Unsupported file format: $extension");
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
}
