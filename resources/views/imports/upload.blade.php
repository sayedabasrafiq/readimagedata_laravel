@extends('imports.layout')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="mb-3">Upload Images</h4>
        <form id="uploadForm" action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="dropzone" class="dropzone mb-3">
                <p class="mb-2">Drag and drop one or more image files</p>
                <input type="file" name="images[]" id="imagesInput" class="form-control" accept="image/*" multiple required>
                <small class="text-muted">You can upload a single image or bulk image files.</small>
            </div>
            @error('images')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('images.*')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary">Start Import</button>
        </form>
    </div>
</div>
<script>
const dropzone = document.getElementById('dropzone');
const input = document.getElementById('imagesInput');

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) {
        input.files = e.dataTransfer.files;
    }
});
</script>
@endsection
