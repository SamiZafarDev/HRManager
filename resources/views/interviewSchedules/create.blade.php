{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/interviewSchedules/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Interview Schedule</h2>

    <form action="{{ route('interviewSchedules.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="interview_date">Interview Date</label>
            <input type="date" id="interview_date" name="interview_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="interview_offset">Interview Offset (minutes)</label>
            <input type="number" id="interview_offset" name="interview_offset" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="number_of_interviews">Number of Interviews</label>
            <input type="number" id="number_of_interviews" name="number_of_interviews" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
