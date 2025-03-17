{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/interviewSchedules/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Interview Schedules</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('interviewSchedules.create') }}" class="btn btn-primary">Add Interview Schedule</a>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Date</th>
                <th>Offset (minutes)</th>
                <th>Number of Interviews</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interviewSchedules as $schedule)
                <tr>
                    <td>{{ $schedule->interview_date }}</td>
                    <td>{{ $schedule->interview_offset }}</td>
                    <td>{{ $schedule->number_of_interviews }}</td>
                    <td>
                        <a href="{{ route('interviewSchedules.edit', $schedule->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('interviewSchedules.destroy', $schedule->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
