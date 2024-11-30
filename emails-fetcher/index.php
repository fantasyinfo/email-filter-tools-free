<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');


error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $server = $_POST['server'];
    $port = $_POST['port'];
    $mailbox = $_POST['mailbox'] ?? 'INBOX';
    $proxy = $_POST['proxy'] ?? '';

    if (!empty($proxy)) {
        putenv("http_proxy=$proxy");
        putenv("https_proxy=$proxy");
    }

    $mailboxConnection = "{{$server}:$port/imap/ssl}$mailbox";

    try {
        $connection = imap_open($mailboxConnection, $email, $password);

        if (!$connection) {
            throw new Exception('Connection failed: ' . imap_last_error());
        }

        $emails = imap_search($connection, 'ALL');

        if ($emails) {
            $csvData = [];

            foreach ($emails as $emailNumber) {
                $header = imap_headerinfo($connection, $emailNumber);

                $from = $header->from[0];
                $emailAddress = $from->mailbox . '@' . $from->host;
                $senderServer = $from->host;

                $csvData[] = [
                    'Email Address' => $emailAddress,
                    'Sender Server' => $senderServer,
                    'Subject' => $header->subject ?? 'No Subject',
                ];
            }

            $filename = 'emails_' . time() . '.csv';
            $file = fopen($filename, 'w');

            fputcsv($file, ['Email Address', 'Sender Server', 'Subject']);

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

            echo "Emails extracted successfully. <a href='$filename' download>Download CSV</a>";
        } else {
            echo "No emails found.";
        }

        imap_close($connection);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Address Extractor</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f9fafc;
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 500px;
                margin: 50px auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            h1 {
                text-align: center;
                color: #333;
                margin-bottom: 20px;
            }

            form {
                display: flex;
                flex-direction: column;
            }

            label {
                font-size: 14px;
                color: #555;
                margin-bottom: 5px;
                font-weight: bold;
            }

            input,
            select,
            button {
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 14px;
                width: 100%;
                box-sizing: border-box;
            }

            input:focus,
            select:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            }

            button {
                background-color: #007bff;
                color: #ffffff;
                border: none;
                cursor: pointer;
                padding: 12px;
                font-size: 16px;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }

            button:hover {
                background-color: #0056b3;
            }

            .note {
                font-size: 14px;
                color: #666;
                text-align: center;
                margin-top: 10px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Email Address Extractor</h1>
            <form method="POST">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email address">

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required placeholder="Enter your email password">

                <label for="server">IMAP Server:</label>
                <input type="text" name="server" id="server" required placeholder="e.g., imap.gmail.com">

                <label for="port">Port:</label>
                <input type="number" name="port" id="port" value="993" required>

                <label for="mailbox">Mailbox (Optional):</label>
                <select name="mailbox" id="mailbox">
                    <option value="INBOX">INBOX</option>
                    <option value="Sent">Sent</option>
                    <option value="Drafts">Drafts</option>
                    <option value="Spam">Spam</option>
                </select>

                <label for="proxy">Proxy (Optional):</label>
                <input type="text" name="proxy" id="proxy" placeholder="http://proxy:port">

                <button type="submit">Extract Email Addresses</button>
            </form>
            <div class="note">
                <p>* Ensure IMAP access is enabled for your email account.<br>* Use an app password if required.</p>
            </div>
        </div>
    </body>

    </html>
<?php
}
?>