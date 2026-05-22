<?php

namespace App\Http\Controllers;

use App\Jobs\ImportQuestionsJob;
use App\Models\PdfImport;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PdfUploadController extends Controller
{
    public function index(): View
    {
        return view('imports.upload');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'max:10240'],
        ]);

        $files = $request->file('images', []);
        $storedPaths = [];
        $originalNames = [];

        foreach ($files as $file) {
            $storedPaths[] = $file->store('uploads/images');
            $originalNames[] = $file->getClientOriginalName();
        }

        $displayName = count($originalNames) === 1
            ? $originalNames[0]
            : sprintf('%d images (%s + %d more)', count($originalNames), $originalNames[0], count($originalNames) - 1);

        $import = PdfImport::create([
            'original_name' => $displayName,
            'stored_path' => json_encode($storedPaths, JSON_THROW_ON_ERROR),
            'status' => 'queued',
            'total_pages' => 0,
            'processed_pages' => 0,
            'total_questions_found' => 0,
            'progress_percent' => 0,
            'error_message' => null,
        ]);

        ImportQuestionsJob::dispatch($import->id)->onQueue('imports');

        return redirect()->route('imports.status', $import->id);
    }

    public function status(int $id): View
    {
        $import = PdfImport::findOrFail($id);

        return view('imports.status', compact('import'));
    }

    public function statusJson(int $id): JsonResponse
    {
        $import = PdfImport::findOrFail($id);

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'total_pages' => $import->total_pages,
            'processed_pages' => $import->processed_pages,
            'total_questions_found' => $import->total_questions_found,
            'progress_percent' => $import->progress_percent,
            'error_message' => $import->error_message,
        ]);
    }

    public function questions(): View
    {
        $questions = Question::with('answers')->latest('id')->paginate(20);

        return view('imports.questions', compact('questions'));
    }

    public function failedJobs(): View
    {
        $failedJobs = \DB::table('failed_jobs')->latest('id')->paginate(20);

        return view('imports.failed-jobs', compact('failedJobs'));
    }
}
