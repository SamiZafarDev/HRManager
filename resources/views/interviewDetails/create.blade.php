{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/interviewDetails/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Interview Detail</h2>

    <form action="{{ route('interviewDetails.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="doc_id">Document ID</label>
            <input type="number" id="doc_id" name="doc_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="end_time">End Time</label>
            <input type="datetime-local" id="end_time" name="end_time" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
