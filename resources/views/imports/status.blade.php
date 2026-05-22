@extends('imports.layout')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="mb-3">Processing Status</h4>
        <p><strong>File:</strong> {{ $import->original_name }}</p>
        <p><strong>Status:</strong> <span id="status">{{ $import->status }}</span></p>
        <div class="progress mb-3" style="height: 24px;">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: {{ $import->progress_percent }}%;">
                {{ $import->progress_percent }}%
            </div>
        </div>
        <div class="row">
            <div class="col-md-3"><strong>Total images:</strong> <span id="totalPages">{{ $import->total_pages }}</span></div>
            <div class="col-md-3"><strong>Processed images:</strong> <span id="processedPages">{{ $import->processed_pages }}</span></div>
            <div class="col-md-3"><strong>Questions found:</strong> <span id="questions">{{ $import->total_questions_found }}</span></div>
            <div class="col-md-3"><strong>Job ID:</strong> #{{ $import->id }}</div>
        </div>
        <div id="queuedHint" class="alert alert-info mt-3 d-none">
            Job is still queued. Make sure queue worker is running: <code>php artisan queue:work --queue=imports</code>
        </div>
        <div id="errorBox" class="alert alert-danger mt-3 d-none"></div>
    </div>
</div>
<script>
const endpoint = "{{ route('imports.status.json', $import->id) }}";
const statusEl = document.getElementById('status');
const bar = document.getElementById('progressBar');
const totalPages = document.getElementById('totalPages');
const processedPages = document.getElementById('processedPages');
const questions = document.getElementById('questions');
const errorBox = document.getElementById('errorBox');
const queuedHint = document.getElementById('queuedHint');
let queuedTicks = 0;

const timer = setInterval(async () => {
    try {
        const response = await fetch(endpoint);
        const data = await response.json();

        statusEl.textContent = data.status;
        bar.style.width = data.progress_percent + '%';
        bar.textContent = data.progress_percent + '%';
        totalPages.textContent = data.total_pages;
        processedPages.textContent = data.processed_pages;
        questions.textContent = data.total_questions_found;

        if (data.error_message) {
            errorBox.classList.remove('d-none');
            errorBox.textContent = data.error_message;
        }

        if (data.status === 'queued') {
            queuedTicks++;
            if (queuedTicks >= 3) {
                queuedHint.classList.remove('d-none');
            }
        } else {
            queuedHint.classList.add('d-none');
        }

        if (data.status === 'failed' || data.status === 'completed') {
            clearInterval(timer);
        }
    } catch (error) {
        errorBox.classList.remove('d-none');
        errorBox.textContent = 'Could not load status update. Check browser console/network and server logs.';
    }
}, 2500);
</script>
@endsection
