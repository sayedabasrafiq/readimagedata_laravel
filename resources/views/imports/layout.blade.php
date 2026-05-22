<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Image OCR Question Importer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .dropzone { border: 2px dashed #0d6efd; border-radius: 10px; background: #fff; padding: 2rem; text-align: center; }
        .dropzone.dragover { background: #e9f2ff; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('imports.upload') }}">Image OCR Question Importer</a>
        <div class="d-flex gap-2">
            <a class="btn btn-light btn-sm" href="{{ route('imports.upload') }}">Home</a>
            <a class="btn btn-light btn-sm" href="{{ route('imports.questions') }}">Questions</a>
            <a class="btn btn-warning btn-sm" href="{{ route('imports.failed-jobs') }}">Failed Jobs</a>
        </div>
    </div>
</nav>
<div class="container pb-5">
    @yield('content')
</div>
</body>
</html>
