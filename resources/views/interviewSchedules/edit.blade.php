{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/interviewSchedules/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Interview Schedule</h2>

    <form action="{{ route('interviewSchedules.update', $interviewSchedule->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="interview_date">Interview Date</label>
            <input type="date" id="interview_date" name="interview_date" class="form-control" value="{{ $interviewSchedule->interview_date }}" required>
        </div>
        <div class="form-group">
            <label for="interview_offset">Interview Offset (minutes)</label>
            <input type="number" id="interview_offset" name="interview_offset" class="form-control" value="{{ $interviewSchedule->interview_offset }}" required>
        </div>
        <div class="form-group">
            <label for="number_of_interviews">Number of Interviews</label>
            <input type="number" id="number_of_interviews" name="number_of_interviews" class="form-control" value="{{ $interviewSchedule->number_of_interviews }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
