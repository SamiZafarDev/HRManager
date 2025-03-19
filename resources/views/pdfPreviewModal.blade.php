<div id="previewModal" class="modal" tabindex="-1"  aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Document Preview</h5>
                <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" onclick="closePreview()">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="documentPreview" style="width: 100%; height: 600px; border: none;"></iframe>
                <div id="unsupportedMessage" style="display: none; color: red;"></div>
            </div>
        </div>
</div>



<!-- CSS for Modal -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: white;
        padding: 20px;
        width: 60vw;
        max-width: 800px;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        text-align: center;
        position: relative;
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: #333;
    }

    .close-btn:hover {
        color: red;
    }
</style>


<script>
function closePreview() {
    document.getElementById('previewModal').style.display = "none";
    document.getElementById('documentPreview').src = ""; // Reset iframe src
}
function previewDocument(fileName) {
    let previewModal = document.getElementById('previewModal');
    let previewFrame = document.getElementById('documentPreview');
    let unsupportedMessage = document.getElementById('unsupportedMessage');

    let baseUrl = window.location.origin;
    let fileUrl = `${baseUrl}/storage/documents/${fileName}`;
    let fileExtension = fileName.split('.').pop().toLowerCase();

    if (["pdf", "jpg", "png"].includes(fileExtension)) {
        previewFrame.src = fileUrl;
        previewFrame.style.display = "block";
        unsupportedMessage.style.display = "none";
    } else {
        previewFrame.style.display = "none";
        unsupportedMessage.innerHTML = "This file type is not supported for preview.";
        unsupportedMessage.style.display = "block";
    }

    previewModal.style.display = "flex"; // Show modal
}
</script>
</script>
