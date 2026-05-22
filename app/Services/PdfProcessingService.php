<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PdfProcessingService
{
    public function __construct(private readonly AiParserHelper $aiParserHelper)
    {
    }

    public function extractTextByPage(array $absoluteImagePaths): array
    {
        $pages = [];

        foreach ($absoluteImagePaths as $index => $path) {
            try {
                if (! is_file($path)) {
                    Log::warning('Image file missing during OCR', ['path' => $path]);
                    $pages[] = '';
                    continue;
                }

                $pages[] = trim((new TesseractOCR($path))->run());
            } catch (\Throwable $e) {
                Log::error('Image OCR failed', [
                    'index' => $index + 1,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
                $pages[] = '';
            }
        }

        return $pages;
    }
}
