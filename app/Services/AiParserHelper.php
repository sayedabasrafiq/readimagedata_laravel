<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AiParserHelper
{
    public function normalizeLine(string $line): string
    {
        $line = preg_replace('/\s+/', ' ', trim($line)) ?? '';
        $line = str_replace(["\xE2\x80\x93", "\xE2\x80\x94"], '-', $line);

        return $line;
    }

    public function isLikelyHeaderOrFooter(string $line): bool
    {
        $normalized = mb_strtolower($this->normalizeLine($line));

        if ($normalized === '' || strlen($normalized) < 2) {
            return true;
        }

        return (bool) preg_match('/^(page\s+\d+|www\.|copyright|exam\s+code|subject\s*:\s*)/i', $normalized);
    }

    public function scoreQuestionStart(string $line): int
    {
        $score = 0;

        if (preg_match('/^(question\s*)?\d+\s*[\.)\-:]/i', $line)) {
            $score += 70;
        }

        if (preg_match('/^q\s*\d+/i', $line)) {
            $score += 60;
        }

        if (preg_match('/\?\s*$/', $line)) {
            $score += 20;
        }

        if (strlen($line) > 8) {
            $score += 10;
        }

        return $score;
    }

    public function isOptionLine(string $line): bool
    {
        return (bool) preg_match('/^([A-Da-d]|[1-4])\s*[\)\.\-:]/', $line);
    }

    public function extractOptionLabel(string $line): ?string
    {
        if (preg_match('/^([A-Da-d]|[1-4])\s*[\)\.\-:]/', $line, $matches)) {
            $raw = strtoupper($matches[1]);

            return match ($raw) {
                '1' => 'A',
                '2' => 'B',
                '3' => 'C',
                '4' => 'D',
                default => $raw,
            };
        }

        return null;
    }
}
