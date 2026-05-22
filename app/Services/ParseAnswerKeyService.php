<?php

namespace App\Services;

class ParseAnswerKeyService
{
    public function parse(array $pages): array
    {
        $joined = implode("\n", $pages);
        $answerMap = [];

        preg_match_all('/\b(\d{1,4})\s*(?:=|\-|:|\.)\s*([1-4A-Da-d])\b/', $joined, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $qNumber = (int) $match[1];
            $raw = strtoupper($match[2]);
            $option = match ($raw) {
                '1' => 'A',
                '2' => 'B',
                '3' => 'C',
                '4' => 'D',
                default => $raw,
            };

            $answerMap[$qNumber] = $option;
        }

        return $answerMap;
    }
}
