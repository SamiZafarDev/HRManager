@extends('layouts.app')

@section('content')

{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/promptInput.blade.php --}}
<div class="container">
    <h2>AI Prompt Settings</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="promptForm" action="{{ route('ai.settings.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="prompt">Enter Ranking Criteria</label>
            <textarea id="prompt" name="prompt" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" id="savePromptBtn" style="display: none;">Save Prompt</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Fetch the stored prompt from the API
    $.ajax({
        url: "{{ route('ai.settings.get') }}",
        type: 'GET',
        success: function(data) {
            if (data.success && data.prompt) {
                $('#prompt').val(data.prompt);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching prompt:", error);
        }
    });

    // Show the "Save Prompt" button only if the content is edited
    $('#prompt').on('input', function() {
        var originalContent = $(this).data('original-content');
        var currentContent = $(this).val();
        if (originalContent !== currentContent) {
            $('#savePromptBtn').show();
        } else {
            $('#savePromptBtn').hide();
        }
    });

    // Store the original content when the form is loaded
    $('#prompt').data('original-content', $('#prompt').val());
});
</script>

@endsection
