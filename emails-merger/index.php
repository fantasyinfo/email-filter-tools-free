<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Chunk Merger</title>
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

        #fileList {
            max-height: 300px;
            overflow-y: auto;
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
                    <h2 class="text-center mb-4">Email Chunk Merger</h2>

                    <form id="emailMergeForm">
                        <div class="mb-3">
                            <label for="emailChunks" class="form-label">Upload Email Chunk Files (txt) Max Files (100)</label>
                            <input class="form-control" type="file" id="emailChunks" accept=".txt" multiple required>
                        </div>

                        <div class="mb-3">
                            <div id="fileList" class="border p-2">
                                <p class="text-muted">No files selected</p>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="removeDuplicates">
                            <label class="form-check-label" for="removeDuplicates">Remove Duplicate Emails</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Merge Email Chunks</button>
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
        document.getElementById('emailChunks').addEventListener('change', function(e) {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = ''; // Clear previous list

            if (this.files.length === 0) {
                fileList.innerHTML = '<p class="text-muted">No files selected</p>';
                return;
            }

            const fileListContent = document.createElement('ul');
            fileListContent.className = 'list-group';

            Array.from(this.files).forEach(file => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerHTML = `
                ${file.name}
                <span class="badge bg-primary rounded-pill">${(file.size / 1024).toFixed(2)} KB</span>
            `;
                fileListContent.appendChild(listItem);
            });

            fileList.appendChild(fileListContent);
        });

        document.getElementById('emailMergeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('emailChunks');
            const removeDuplicates = document.getElementById('removeDuplicates').checked;
            const resultContainer = document.getElementById('resultContainer');
            const loader = document.getElementById('loader');

            if (!fileInput.files.length) {
                alert('Please select email chunk files');
                return;
            }

            const formData = new FormData();
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('email_chunks[]', fileInput.files[i]);
            }
            formData.append('remove_duplicates', removeDuplicates ? '1' : '0');

            loader.style.display = 'block';
            resultContainer.innerHTML = '';

            fetch('email_merger.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loader.style.display = 'none';

                    if (data.success) {
                        resultContainer.innerHTML = `
                    <div class="alert alert-success">
                        <h5>Merge Complete!</h5>
                        <p>Total Emails: ${data.total_emails}</p>
                        <p>Unique Emails: ${data.unique_emails}</p>
                        <a href="email_merger.php?download=${data.merged_file}" 
                           class="btn btn-primary">
                            Download Merged Emails
                        </a>
                    </div>
                `;
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