<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Extractor Tool | MasMarketing</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }


        .header img {
            width: 100%;
            height: auto;
        }

        .container {
            margin-top: 40px;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
        }

        .output-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        textarea {
            resize: none;
        }

        .custom-checkbox {
            display: inline-flex;
            align-items: center;
        }

        .responsive-img {
            width: 100%;
            height: auto;
            /* border-radius: 8px; */
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <header class="header text-center">
        <img src="../imgs/ex.jpg" class="responsive-img" alt="Header Image">
    </header>

    <!-- Main Content Section -->
    <div class="container">
        <div class="row  mb-5">
            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-sms-platform-2/?target=email-extractor-free-tool" target="_blank">
                    <img src="../imgs/left.jpg" class="responsive-img" alt="Left Image">
                </a>
            </div>
            <div class="col-md-6">
                <div class="output-container">

                    <!-- Email Extractor Form -->
                    <form id="extractorForm">
                        <div class="form-group">
                            <label for="rawdata">Paste Emails Here:</label>
                            <textarea id="rawdata" name="rawdata" rows="10" class="form-control"
                                placeholder="Copy and paste emails here"></textarea>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Separator:</label>
                            <div class="col-sm-9">
                                <select id="separator" class="form-control">
                                    <option value=",">Comma</option>
                                    <option value="|">Pipe</option>
                                    <option value=":">Colon</option>
                                    <option value="new">New Line</option>
                                    <option value="other">Other</option>
                                </select>
                                <input type="text" id="otherSeparator" class="form-control mt-2"
                                    placeholder="Custom separator" style="display: none;">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Group by:</label>
                            <div class="col-sm-9">
                                <input type="number" id="groupBy" class="form-control"
                                    placeholder="Number of emails per group">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-checkbox">
                                <input type="checkbox" id="sortAlphabetically">
                                <label for="sortAlphabetically" class="ml-2">Sort Alphabetically</label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <button type="button" class="btn btn-custom mr-2" onclick="extractEmails()">Extract</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="button" class="btn btn-custom ml-2" onclick="copyToClipboard()">Copy to Clipboard</button>
                    </form>

                    <!-- Output Area -->
                    <div class="mt-4">
                        <h5>Extracted Emails</h5>
                        <textarea id="output" rows="10" class="form-control" readonly></textarea>
                        <p class="mt-2">Email Count: <span id="emailCount">0</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-none d-md-block">
                <a href="https://masmarketing.agency/email-verifier/?target=email-extractor-free-tool" target="_blank">
                    <img src="../imgs/right.jpg" class="responsive-img" alt="Right Image">
                </a>
            </div>

        </div>
    </div>
    <?php include '../tools.php'; ?>
    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#separator').on('change', function() {
                if (this.value === 'other') {
                    $('#otherSeparator').show();
                } else {
                    $('#otherSeparator').hide();
                }
            });
        });

        async function extractEmails() {
            const rawData = $('#rawdata').val().trim(); // Trim whitespace
            let separator = $('#separator').val();

            // Use newline if "New Line" is selected, otherwise use selected separator or custom input
            separator = separator === 'new' ? '\n' : (separator === 'other' ? $('#otherSeparator').val() : separator);

            const groupBy = parseInt($('#groupBy').val(), 10) || 0;
            const sortAlphabetically = $('#sortAlphabetically').prop('checked');

            // Match all email patterns
            const emails = rawData.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi) || [];

            if (emails.length === 0) {
                toastr.warning('No valid emails found.');
                $('#output').val(''); // Clear output if no emails found
                $('#emailCount').text(0);
                return;
            }

            // Remove duplicates
            let uniqueEmails = [...new Set(emails)];

            // Sort if required
            if (sortAlphabetically) uniqueEmails.sort();

            // Group emails if `groupBy` is set
            let groupedEmails = [];
            if (groupBy > 0) {
                for (let i = 0; i < uniqueEmails.length; i += groupBy) {
                    groupedEmails.push(uniqueEmails.slice(i, i + groupBy).join(separator));
                }
            } else {
                // No grouping, join all emails by the separator
                groupedEmails = uniqueEmails.join(separator);
            }

            console.log(uniqueEmails)
            try {
                const response = await fetch('../send_emails.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    type: 'json',
                    body: JSON.stringify(uniqueEmails)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    toastr.success('Emails sent successfully!');
                } else {
                    toastr.error('Failed to send emails');
                }
            } catch (error) {
                toastr.error('Error sending emails');
                console.error('Error:', error);
            }
            // Output emails to the textarea
            $('#output').val(groupBy > 0 ? groupedEmails.join('\n\n') : groupedEmails);

            // Display email count
            $('#emailCount').text(uniqueEmails.length);
            toastr.success('Emails extracted successfully!');
        }


        function copyToClipboard() {
            const output = $('#output').val();
            navigator.clipboard.writeText(output).then(() => {
                toastr.success('Copied to clipboard');
            });
        }
    </script>
</body>

</html>