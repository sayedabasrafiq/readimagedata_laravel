@extends('imports.layout')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="mb-3">Imported Questions</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Answers</th>
                </tr>
                </thead>
                <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td>{{ $question->id }}</td>
                        <td>{{ $question->title }}</td>
                        <td>
                            @foreach($question->answers as $answer)
                                <div class="{{ $answer->is_correct ? 'text-success fw-bold' : '' }}">
                                    {{ $answer->answer }}
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">No questions imported yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $questions->links() }}
    </div>
</div>
@endsection
