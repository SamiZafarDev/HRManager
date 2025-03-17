<?php

use App\Enums\StorageFolder;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToImage\Pdf as PdfToImage;
use Illuminate\Support\Facades\Storage;

class DocumentHelper
{
    public function convertToPDF($filename, StorageFolder $StorageFolder = StorageFolder::UPLOADS)
    {
        $path = storage_path("{$StorageFolder->publicPath()}/{$filename}");

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $pdfFilename = pathinfo($filename, PATHINFO_FILENAME) . ".pdf";
        $pdfPath = storage_path("app/public/documents/{$pdfFilename}");

        if (in_array($fileExtension, ['docx', 'doc'])) {
            // Convert DOCX/DOC to PDF
            $phpWord = IOFactory::load($path);
            $pdf = Pdf::loadHTML($phpWord->save('php://output', 'HTML'));
            Storage::put("{$StorageFolder->publicPath()}/{$pdfFilename}", $pdf->output());
        } elseif (in_array($fileExtension, ['jpg', 'png'])) {
            // Convert Image to PDF
            $pdf = Pdf::loadHTML('<img src="'.asset("{$StorageFolder->publicPath()}/{$filename}").'" style="width:100%;">');
            Storage::put("{$StorageFolder->publicPath()}/{$pdfFilename}", $pdf->output());
        } else {
            return response()->json(['error' => 'Unsupported file format for PDF conversion'], 400);
        }

        return response()->json(['message' => 'File converted', 'pdf_url' => asset("{$StorageFolder->publicPath()}/{$pdfFilename}")]);
    }
}
