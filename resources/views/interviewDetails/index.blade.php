@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Interview Details</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#interviewFormModal">Add Interview Detail</a> --}}

    <table class="table mt-3 table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Document ID</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interviewDetails as $detail)
                <tr>
                    <td>{{ $detail->name }}</td>
                    <td>{{ $detail->email }}</td>
                    <td>{{ $detail->doc_id }}</td>
                    <td>{{ $detail->start_time }}</td>
                    <td>{{ $detail->end_time }}</td>
                    <td>
                        <a href="{{ route('interviewDetails.edit', $detail->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('interviewDetails.destroy', $detail->id) }}" method="POST" style="display:inline-block;">
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

