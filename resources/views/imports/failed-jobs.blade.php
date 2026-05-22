@extends('imports.layout')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="mb-3">Failed Jobs</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Failed At</th>
                    <th>Exception</th>
                </tr>
                </thead>
                <tbody>
                @forelse($failedJobs as $job)
                    <tr>
                        <td>{{ $job->id }}</td>
                        <td>{{ $job->queue }}</td>
                        <td>{{ $job->failed_at }}</td>
                        <td><pre class="mb-0" style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($job->exception, 500) }}</pre></td>
                    </tr>
                @empty
                    <tr><td colspan="4">No failed jobs.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $failedJobs->links() }}
    </div>
</div>
@endsection

