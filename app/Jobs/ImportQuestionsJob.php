<?php

namespace App\Jobs;

use App\Models\Answer;
use App\Models\PdfImport;
use App\Models\Question;
use App\Services\ExtractQuestionsService;
use App\Services\ParseAnswerKeyService;
use App\Services\PdfProcessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportQuestionsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 7200;

    public function __construct(public readonly int $importId)
    {
    }

    public function handle(
        PdfProcessingService $pdfProcessingService,
        ExtractQuestionsService $extractQuestionsService,
        ParseAnswerKeyService $parseAnswerKeyService
    ): void {
        $import = PdfImport::findOrFail($this->importId);

        try {
            $import->update([
                'status' => 'processing',
                'started_at' => now(),
                'progress_percent' => 5,
                'error_message' => null,
            ]);

            $stored = json_decode((string) $import->stored_path, true);
            $storedPaths = is_array($stored) ? $stored : [$import->stored_path];
            $storedPaths = array_values(array_filter($storedPaths, static fn ($p): bool => is_string($p) && $p !== ''));

            if (count($storedPaths) === 0) {
                throw new RuntimeException('No uploaded images were found for this job. Please upload again.');
            }

            $diskName = config('filesystems.default', 'local');
            $disk = Storage::disk($diskName);

            $missing = array_filter($storedPaths, static fn (string $relativePath): bool => ! $disk->exists($relativePath));
            if (count($missing) > 0) {
                throw new RuntimeException('One or more uploaded image files are missing on disk. Please upload the images again.');
            }

            $absolutePaths = array_map(static fn (string $relativePath): string => $disk->path($relativePath), $storedPaths);

            $pages = $pdfProcessingService->extractTextByPage($absolutePaths);
            $nonEmptyPages = array_filter($pages, static fn (string $page): bool => trim($page) !== '');

            if (count($pages) === 0 || count($nonEmptyPages) === 0) {
                throw new RuntimeException(
                    'Could not read text from uploaded image(s). Ensure image files are clear and not heavily blurred.'
                );
            }

            $import->update([
                'total_pages' => count($pages),
                'processed_pages' => 0,
                'progress_percent' => 20,
            ]);

            $parsedQuestions = $extractQuestionsService->extract($pages);
            $answerMap = $parseAnswerKeyService->parse($pages);

            $total = count($parsedQuestions);
            $saved = 0;

            if ($total === 0) {
                throw new RuntimeException(
                    'Text was extracted from image(s), but no questions matched the expected format. Check image layout/quality or parser rules.'
                );
            }

            foreach ($parsedQuestions as $index => $parsedQuestion) {
                DB::transaction(function () use ($parsedQuestion, $answerMap, &$saved): void {
                    $question = Question::create([
                        'title' => $parsedQuestion['title'],
                        'description' => null,
                        'question_type' => 'Selective',
                        'points' => 5,
                        'level_id' => 1,
                        'category_id' => 1,
                        'is_approve' => 1,
                        'subject_id' => 1,
                        'language_id' => 1,
                        'created_by' => 1,
                    ]);

                    $correct = $answerMap[$parsedQuestion['number']] ?? null;

                    foreach ($parsedQuestion['options'] as $label => $answerText) {
                        if ($answerText === '') {
                            continue;
                        }

                        Answer::create([
                            'answer' => $answerText,
                            'is_correct' => $correct !== null && $label === $correct,
                            'question_id' => $question->id,
                        ]);
                    }

                    $saved++;
                });

                $processedPages = min(count($pages), $index + 1);
                $progress = $total > 0 ? 20 + (int) floor((($index + 1) / $total) * 75) : 95;

                $import->update([
                    'processed_pages' => $processedPages,
                    'total_questions_found' => $saved,
                    'progress_percent' => min($progress, 95),
                ]);
            }

            $import->update([
                'status' => 'completed',
                'processed_pages' => count($pages),
                'total_questions_found' => $saved,
                'progress_percent' => 100,
                'error_message' => null,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $import->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('Image import attempt failed', [
                'import_id' => $this->importId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $import = PdfImport::find($this->importId);

        if ($import !== null) {
            $import->update([
                'status' => 'failed',
                'error_message' => $exception?->getMessage() ?? $import->error_message,
                'finished_at' => now(),
            ]);
        }

        Log::error('Import failed', [
            'import_id' => $this->importId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
