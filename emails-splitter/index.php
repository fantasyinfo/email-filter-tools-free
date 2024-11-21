<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email File Splitter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .landing-header img {
            width: 100%;
            height: auto;
        }

        .upload-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }

        .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="landing-header">
        <img src="../imgs/er.jpg" class="img-fluid" alt="Header Image">
    </div>
    <div id="loader">
        <div class="spinner"></div>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-sms-platform-2/?target=email-remover-free-tool" target="_blank">
                    <img src="../imgs/left.jpg" class="img-fluid" alt="Left Image">
                </a>
            </div>
            <div class="col-md-6 mb-5">
                <div class="upload-container">
                    <h2 class="text-center mb-4">Email File Splitter</h2>

                    <form id="emailSplitForm">
                        <div class="mb-3">
                            <label for="emailFile" class="form-label">Upload Email File (txt/csv)</label>
                            <input class="form-control" type="file" id="emailFile" accept=".txt,.csv" required>
                        </div>

                        <div class="mb-3">
                            <label for="splitOption" class="form-label">Split Size</label>
                            <select id="splitOption" class="form-select" required>
                                <option value="">Select Split Option</option>
                                <option value="100">100 Emails per File</option>
                                <option value="500">500 Emails per File</option>
                                <option value="1000" selected>1,000 Emails per File</option>
                                <option value="2500">2,500 Emails per File</option>
                                <option value="5000">5,000 Emails per File</option>
                                <option value="10000">10,000 Emails per File</option>
                                <option value="25000">25,000 Emails per File</option>
                                <option value="50000">50,000 Emails per File</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Split Emails</button>
                        </div>
                    </form>

                    <div id="resultContainer" class="mt-3"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('emailSplitForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('emailFile');
            const splitOption = document.getElementById('splitOption').value;
            const resultContainer = document.getElementById('resultContainer');
            const loader = document.getElementById('loader');

            if (!fileInput.files.length) {
                alert('Please select a file');
                return;
            }

            const formData = new FormData();
            formData.append('email_file', fileInput.files[0]);
            formData.append('split_size', splitOption);

            loader.style.display = 'block';
            resultContainer.innerHTML = '';

            fetch('email_splitter.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loader.style.display = 'none';

                    if (data.success) {
                        const info = data.data;
                        let resultHTML = `
                    <div class="alert alert-success">
                        <h5>Split Complete!</h5>
                        <p>Total Emails: ${info.total_emails}</p>
                        <p>Number of Chunks: ${info.chunk_count}</p>
                        <div class="mt-3">
                            <h6>Download Options:</h6>
                `;

                        // Individual chunk downloads
                        info.chunks.forEach((chunk, index) => {
                            resultHTML += `
                        <a href="email_splitter.php?download=${chunk}" 
                           class="btn btn-outline-primary btn-sm m-1">
                            Download Chunk ${index + 1}
                        </a>
                    `;
                        });

                        // ZIP download
                        resultHTML += `
                    <a href="email_splitter.php?download=${info.zip_file}" 
                       class="btn btn-success btn-sm m-1">
                        Download All Chunks (ZIP)
                    </a>
                    </div>
                </div>`;

                        resultContainer.innerHTML = resultHTML;
                    } else {
                        resultContainer.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.error}
                    </div>
                `;
                    }
                })
                .catch(error => {
                    loader.style.display = 'none';
                    console.error('Error:', error);
                    resultContainer.innerHTML = `
                <div class="alert alert-danger">
                    An unexpected error occurred. Please try again.
                </div>
            `;
                });
        });
    </script>
</body>

</html>