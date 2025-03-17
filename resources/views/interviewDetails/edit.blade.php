{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/interviewDetails/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Interview Detail</h2>

    <form action="{{ route('interviewDetails.update', $interviewDetail->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ $interviewDetail->name }}" required readonly>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ $interviewDetail->email }}" required readonly>
        </div>
        <div class="form-group">
            <label for="doc_id">Document ID</label>
            <input type="number" id="doc_id" name="doc_id" class="form-control" value="{{ $interviewDetail->doc_id }}" required readonly>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="datetime-local" id="start_time" name="start_time" class="form-control" value="{{ $interviewDetail->start_time }}" required>
        </div>
        <div class="form-group">
            <label for="end_time">End Time</label>
            <input type="datetime-local" id="end_time" name="end_time" class="form-control" value="{{ $interviewDetail->end_time }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
