@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Email Template</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="emailTemplateForm" action="{{ route('emailHandler.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email_template">Enter Email Template</label>
            <textarea id="email_template" name="email_template" class="form-control" rows="10" required>{{ $staticEmail ? $staticEmail->email_template : '' }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary" id="saveEmailTemplateBtn">Save Template</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Fetch the stored email template from the API
    $.ajax({
        url: "{{ route('emailHandler.get') }}",
        type: 'GET',
        success: function(data) {
            if (data.success && data.email_template) {
                $('#email_template').val(data.email_template);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching email template:", error);
        }
    });
});
</script>

@endsection
