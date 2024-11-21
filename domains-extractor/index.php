<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Extractor Tool</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        .landing-header img {
            width: 100%;
            height: auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .drop-zone {
            border: 2px dashed #ccc;
            transition: all 0.3s ease;
        }

        .drop-zone.dragover {
            border-color: #3498db;
            background-color: rgba(52, 152, 219, 0.1);
        }

        #resultBox {
            height: 300px;
            overflow-y: scroll;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Landing Page Header -->
    <div class="landing-header">
        <img src="../imgs/er.jpg" class="img-fluid" alt="Header Image">
    </div>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-sms-platform-2/?target=email-remover-free-tool" target="_blank">
                    <img src="../imgs/left.jpg" class="img-fluid" alt="Left Image">
                </a>
            </div>
            <div class="col-md-6 mb-5">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h1 class="text-center mb-4 animate__animated animate__fadeIn">Domain Extractor Tool</h1>

                        <div id="dropZone" class="drop-zone text-center p-4 mb-3 rounded">
                            <div class="text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mb-3" width="50" height="50" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 0 .5.5H5v4.5a.5.5 0 0 0 1 0V10.4h4v4.5a.5.5 0 0 0 1 0V10.4h4a.5.5 0 0 0 .5-.5V9.6H1v.3zm5.646-3.146a.5.5 0 0 0 0 .708L8 10.707l2.354-2.355a.5.5 0 1 0-.708-.708L8.5 8.793V1.5a.5.5 0 0 0-1 0v7.293L6.354 7.354a.5.5 0 0 0-.708 0z" />
                                </svg>
                                <p>Drop your file here or click to upload</p>
                                <small class="text-muted">Supports .txt files</small>
                            </div>
                        </div>

                        <textarea id="domainInput" class="form-control mb-3" rows="4" placeholder="Or paste your domains here (one per line)..."></textarea>

                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <button id="extractButton" class="btn btn-primary">Extract Domains</button>
                            <div class="loader ms-3" id="loader"></div>
                        </div>

                        <div id="resultSection" class="mt-4 d-none">
                            <h5>Extracted Domains:</h5>
                            <div id="resultBox" class="bg-light border rounded p-3 mb-3">
                                <pre id="extractedDomains" class="mb-0"></pre>
                            </div>
                            <div class="text-center">
                                <button id="downloadButton" class="btn btn-success">Download Results</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-verifier/?target=email-remover-free-tool" target="_blank">
                    <img src="../imgs/right.jpg" class="img-fluid" alt="Right Image">
                </a>
            </div>
        </div>
    </div>
    <?php include '../tools.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add your JavaScript from the original code here
        $(document).ready(function() {
            const dropZone = $('#dropZone');
            const domainInput = $('#domainInput');
            const extractButton = $('#extractButton');
            const loader = $('#loader');
            const resultSection = $('#resultSection');
            const extractedDomains = $('#extractedDomains');
            const downloadButton = $('#downloadButton');

            // Drag and drop handlers
            dropZone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            dropZone.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            dropZone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');

                const file = e.originalEvent.dataTransfer.files[0];
                if (file) {
                    readFile(file);
                }
            });

            dropZone.on('click', function() {
                const input = $('<input type="file" accept=".txt" style="display: none;">');
                input.click();

                input.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        readFile(file);
                    }
                });
            });

            function readFile(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    domainInput.val(e.target.result);
                };
                reader.readAsText(file);
            }

            extractButton.on('click', function() {
                const domains = domainInput.val().trim();
                if (!domains) {
                    alert('Please enter or upload domains first!');
                    return;
                }

                extractButton.prop('disabled', true);
                loader.show();

                $.ajax({
                    url: 'extract.php',
                    method: 'POST',
                    data: {
                        domains: domains
                    },
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            extractedDomains.text(result.domains.join('\n'));
                            resultSection.removeClass('d-none').addClass('animate__animated animate__fadeIn');
                        } else {
                            alert('Error: ' + result.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Server error: ' + textStatus);
                    },
                    complete: function() {
                        extractButton.prop('disabled', false);
                        loader.hide();
                    }
                });
            });

            downloadButton.on('click', function() {
                const domains = extractedDomains.text();
                const blob = new Blob([domains], {
                    type: 'text/plain'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'extracted_domains.txt';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            });
        });
    </script>
</body>

</html>