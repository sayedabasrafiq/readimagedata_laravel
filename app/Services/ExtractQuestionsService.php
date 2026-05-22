<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ExtractQuestionsService
{
    public function __construct(private readonly AiParserHelper $aiParserHelper)
    {
    }

    public function extract(array $pages): array
    {
        $questions = [];
        $current = null;
        $currentOption = null;

        foreach ($pages as $pageIdx => $pageText) {
            $lines = preg_split('/\R/', (string) $pageText) ?: [];

            foreach ($lines as $line) {
                $line = $this->aiParserHelper->normalizeLine($line);
                if ($this->aiParserHelper->isLikelyHeaderOrFooter($line)) {
                    continue;
                }

                $questionNumber = $this->parseQuestionNumber($line);
                if ($questionNumber !== null && $this->aiParserHelper->scoreQuestionStart($line) >= 60) {
                    if ($current !== null) {
                        $questions[] = $this->finalizeQuestion($current);
                    }

                    $current = [
                        'number' => $questionNumber,
                        'title' => trim(preg_replace('/^(question\s*)?q?\s*\d+\s*[\.)\-:]\s*/i', '', $line) ?? ''),
                        'options' => ['A' => '', 'B' => '', 'C' => '', 'D' => ''],
                    ];
                    $currentOption = null;
                    continue;
                }

                if ($current === null) {
                    continue;
                }

                if ($this->aiParserHelper->isOptionLine($line)) {
                    $label = $this->aiParserHelper->extractOptionLabel($line);
                    if ($label !== null) {
                        $currentOption = $label;
                        $current['options'][$label] = trim(preg_replace('/^([A-Da-d]|[1-4])\s*[\)\.\-:]\s*/', '', $line) ?? '');
                    }
                    continue;
                }

                if ($currentOption !== null) {
                    $current['options'][$currentOption] = trim($current['options'][$currentOption] . ' ' . $line);
                } else {
                    $current['title'] = trim($current['title'] . ' ' . $line);
                }
            }

            Log::debug('Page parsed for questions', ['page' => $pageIdx + 1]);
        }

        if ($current !== null) {
            $questions[] = $this->finalizeQuestion($current);
        }

        return array_values(array_filter($questions, function (array $q): bool {
            return $q['title'] !== '' && count(array_filter($q['options'])) >= 2;
        }));
    }

    private function parseQuestionNumber(string $line): ?int
    {
        if (preg_match('/^(?:question\s*)?(?:q\s*)?(\d{1,4})\s*[\.)\-:]/i', $line, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function finalizeQuestion(array $question): array
    {
        $question['title'] = trim($question['title']);
        foreach ($question['options'] as $key => $value) {
            $question['options'][$key] = trim($value);
        }

        return $question;
    }
}
