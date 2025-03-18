@extends('layouts.app')

@section('content')
{{-- filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/resources/views/rankDocumentsDisplayView.blade.php --}}
<h2>Document Details List</h2>
<button id="fetchDataBtn" class="btn btn-primary">Fetch Document Details</button>

<table id="documentTable">
    <thead>
        <tr>
            <th>Document Name</th>
            <th>Rank</th>
            <th>Status</th>
            <th>Preview</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data will be inserted here dynamically -->
    </tbody>
</table>

@endsection

<!-- Interview Form Modal -->
<div id="interviewFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Interview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="interviewForm" action="{{ route('interviewDetails.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="name" class="col-form-label fw-bold">Name:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" id="name" name="name" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="email" class="col-form-label fw-bold">Email:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" id="email" name="email" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="doc_id" class="col-form-label fw-bold">Document ID:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="number" id="doc_id" name="doc_id" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="start_time" class="col-form-label fw-bold">Start Time:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="end_time" class="col-form-label fw-bold">End Time:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="datetime-local" id="end_time" name="end_time" class="form-control" required>
                        </div>
                    </div>

                    <div class="text-end">
                        {{-- <button type="button" class="btn btn-secondary me-2" data-dismiss="modal">Cancel</button> --}}
                        <button type="submit" class="btn btn-primary">Save Interview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('pdfPreviewModal')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    function fetchRankedDocuments() {
        $.ajax({
            url: "{{ route('document.details') }}",
            type: 'GET',
            success: function(data) {
                console.log("Fetched Data:", data);
                renderDocumentDetails(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching document details:", error);
            }
        });
    }
    $('#fetchDataBtn').on('click', fetchRankedDocuments);
    fetchRankedDocuments();

    function renderDocumentDetails(documentDetails) {
        let tableBody = $("#documentTable tbody");
        tableBody.empty();

        if (!documentDetails || documentDetails.length === 0) {
            tableBody.append(`<tr><td colspan="6" style="text-align:center;">No documents found</td></tr>`);
            return;
        }

        documentDetails.forEach(doc => {
            let row = $('<tr></tr>');

            let nameCell = $('<td></td>').text(doc.doc.name);
            row.append(nameCell);

            let rankCell = $('<td></td>').text(doc.rank);
            row.append(rankCell);

            let statusCell = $('<td></td>').text(doc.stats);
            row.append(statusCell);

            let previewCell = $('<td></td>');
            let previewButton = $('<button></button>').text("Preview").addClass("btn btn-secondary").on('click', function() {
                previewDocument(doc.doc.name);
            });
            previewCell.append(previewButton);
            row.append(previewCell);

            let emailCell = $('<td></td>');
            if (doc.email) {
                emailCell.text(doc.email);

                // Send Email Btn
                // let emailButton = $('<button></button>').text("Send Email").addClass("btn btn-primary").on('click', function() {
                //     sendEmail(doc.email, row);
                // });
                // emailCell.append('<br>').append(emailButton);
            } else {
                emailCell.text("Email not found");
            }
            row.append(emailCell);

            let actionCell = $('<td></td>');
            let scheduleButton = $('<button></button>').text("Schedule Interview").addClass("btn btn-success").on('click', function() {
                populateInterviewForm(doc);
                $('#interviewFormModal').modal('show');
            });
            actionCell.append(scheduleButton);
            row.append(actionCell);

            tableBody.append(row);
        });
    }

    function populateInterviewForm(doc) {
        $('#name').val(doc.doc.name);
        $('#email').val(doc.email);
        $('#doc_id').val(doc.doc.id);
    }

    function sendEmail(email, row) {
        if (confirm("Are you sure you want to send an email to " + email + "?")) {
            $.ajax({
                url: "{{ route('sendEmailscheduleInterview') }}",
                type: 'POST',
                data: {
                    email: email,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Email sent successfully.');
                    } else {
                        alert('Failed to send email.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending email:', error);
                    alert('An error occurred while sending the email.');
                }
            });
        }
    }
});
</script>
