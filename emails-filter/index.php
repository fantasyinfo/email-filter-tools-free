<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Filter - Landing Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .landing-header img {
            width: 100%;
            height: auto;
        }

        .upload-zone {
            border: 2px dashed #6c757d;
            padding: 3rem 1rem;
            text-align: center;
            background-color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 8px;
        }

        .upload-zone:hover {
            background-color: #f1f1f1;
        }

        .btn-upload {
            margin-top: 1rem;
        }

        .loader {
            display: none;
        }

        .responsive-img {
            width: 100%;
            height: auto;
            /* border-radius: 8px; */
        }

        @media (max-width: 768px) {
            .upload-zone {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>

<body>

    <!-- Landing Page Header -->
    <div class="landing-header">
        <img src="../imgs/er.jpg" class="responsive-img" alt="Header Image">
    </div>

    <!-- Upload Section -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-sms-platform-2/?target=email-remover-free-tool" target="_blank">
                    <img src="../imgs/left.jpg" class="responsive-img" alt="Left Image">
                </a>
            </div>

            <div class="col-md-6 mb-5">
                <h3 class="text-center mb-3">Upload Your Email List</h3>

                <div id="email-upload-zone" class="upload-zone mb-4">Drag or click to upload email file</div>
                <input type="file" id="email-file" style="display: none;">

                <div id="spam-upload-zone" class="upload-zone mb-4">Drag or click to upload remove emails file</div>
                <input type="file" id="spam-file" style="display: none;">

                <button id="submit-btn" class="btn btn-primary w-100 btn-upload" disabled>Filter Emails</button>

                <div class="loader text-center mt-3">
                    <img src="loader.gif" alt="Loading...">
                </div>

                <div class="mt-4" id="results" style="display: none;">
                    <h4>Download Filtered Results:</h4>
                    <a download id="download-valid" class="btn btn-success w-100 mb-2">Valid Emails</a>
                    <a download id="download-spam" class="btn btn-danger w-100 mb-2">Remove Emails</a>
                    <a download id="download-duplicate" class="btn btn-warning w-100">Duplicate Emails</a>
                </div>
            </div>

            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-verifier/?target=email-remover-free-tool" target="_blank">
                    <img src="../imgs/right.jpg" class="responsive-img" alt="Right Image">
                </a>
            </div>

        </div>

    </div>
    <?php include '../tools.php'; ?>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            let emailFile = null;
            let spamFile = null;

            function handleFileSelect(e, input) {
                e.preventDefault();
                e.stopPropagation();
                let files = e.originalEvent.dataTransfer.files;
                input.files = files;

                if (files.length > 0) {
                    $(input).trigger('change');
                }
            }

            function addDragAndDropFunctionality(uploadZone, fileInputId) {
                $(uploadZone).off('dragover').on('dragover', function(e) {
                    e.preventDefault();
                    $(this).css('background-color', '#f1f1f1');
                }).off('dragleave').on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).css('background-color', 'white');
                }).off('drop').on('drop', function(e) {
                    handleFileSelect(e, $(fileInputId)[0]);
                });

                $(uploadZone).off('click').on('click', function() {
                    $(fileInputId).click();
                });
            }

            addDragAndDropFunctionality('#email-upload-zone', '#email-file');
            addDragAndDropFunctionality('#spam-upload-zone', '#spam-file');

            $('#email-file').off('change').on('change', function() {
                emailFile = this.files[0];
                if (emailFile) {
                    $('#submit-btn').prop('disabled', false);
                    $('#email-upload-zone').text(emailFile.name);
                }
            });

            $('#spam-file').off('change').on('change', function() {
                spamFile = this.files[0];
                if (spamFile) {
                    $('#spam-upload-zone').text(spamFile.name);
                }
            });

            $('#submit-btn').off('click').on('click', function() {
                if (!emailFile) return;

                let formData = new FormData();
                formData.append('emailFile', emailFile);
                if (spamFile) formData.append('spamFile', spamFile);

                $('#submit-btn').prop('disabled', true);
                $('.loader').show();

                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('.loader').hide();
                        $('#submit-btn').prop('disabled', false);

                        let result = JSON.parse(response);
                        if (result.success) {
                            $('#results').show();
                            $('#download-valid').attr('href', result.validFile);
                            $('#download-spam').attr('href', result.spamFile);
                            $('#download-duplicate').attr('href', result.duplicateFile);
                        }
                    },
                    error: function() {
                        alert('Error processing the files.');
                        $('.loader').hide();
                        $('#submit-btn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>

</html>