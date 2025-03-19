@extends('layouts.app')

@section('content')

<div class="container">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mt-4">
        <div class="card-header">
            <h3>Rank Documents</h3>
        </div>
        <div class="card-body">
            <form id="rankDocumentsForm" action="{{ route('rankDocuments') }}" method="GET">
                @csrf
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Start Ranking</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3>Document Details List</h3>
        </div>
        <div class="card-body">
            <button id="fetchDocumentsBtn" class="btn btn-primary mb-3">Fetch Document Details</button>

            <table id="documentDisplayTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Preview</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be inserted here dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#rankDocumentsForm').on('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    $.ajax({
        url: $(this).attr('action'),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        success: function(data) {
            console.log("On ranking data: " +data);
            if (data.success) {
                alert('Documents ranked successfully.');
            } else {
                alert('Failed to rank documents.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('An error occurred while ranking documents.');
        }
    });
});
</script>

@include('pdfPreviewModal')

<script>
function fetchData() {
    $.ajax({
        url: "{{ route('getMyDocuments') }}",
        type: 'GET',
        success: function(data) {
            console.log("Fetched Data:", data);
            localStorage.setItem('documentsData', JSON.stringify(data)); // Store in localStorage
            renderdocumentsData(data);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching document details:", error);
        }
    });
}
$('#fetchDocumentsBtn').on('click', fetchData);
fetchData();

$(document).ready(function() {
    let storedData = localStorage.getItem('documentsData');
    if (storedData) {
        let documentsData = JSON.parse(storedData);
        renderdocumentsData(documentsData);
    }
});

function renderdocumentsData(documentsData) {
    let tableBody = $("#documentDisplayTable tbody");
    tableBody.empty();

    if (!documentsData || documentsData.length === 0) {
        tableBody.append(`<tr><td colspan="5" style="text-align:center;">No documents found</td></tr>`);
        return;
    }

    documentsData.forEach(doc => {
        let row = $('<tr></tr>');

        let nameCell = $('<td></td>').text(doc.name);
        row.append(nameCell);

        let previewCell = $('<td></td>');
        let previewButton = $('<button></button>').text("Preview").addClass("btn btn-secondary").on('click', function() {
            previewDocument(doc.name);
        });
        previewCell.append(previewButton);
        row.append(previewCell);

        let deleteCell = $('<td></td>');
        let deleteButton = $('<button></button>').text("Delete").addClass("btn btn-danger").on('click', function() {
            deleteDocument(doc.id, row);
        });
        deleteCell.append(deleteButton);
        row.append(deleteCell);

        tableBody.append(row);
    });
}

function deleteDocument(docId, row) {
    if (confirm("Are you sure you want to delete this document?")) {
        $.ajax({
            url: "{{ route('document.delete') }}",
            type: 'DELETE',
            data: {
                doc_id: docId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    row.remove(); // Remove the row from the table
                    alert('Document deleted successfully.');
                } else {
                    alert('Failed to delete document.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting document:', error);
                alert('An error occurred while deleting the document.');
            }
        });
    }
}
</script>
@endsection
