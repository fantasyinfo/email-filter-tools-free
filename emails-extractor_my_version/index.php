<?php
class EmailExtractor
{
    private $connection;
    private $email;
    private $password;
    private $server;
    private $port;
    private $protocol;

    public function __construct($email, $password, $server, $port, $protocol = 'imap')
    {
        $this->email = $email;
        $this->password = $password;
        $this->server = $server;
        $this->port = $port;
        $this->protocol = strtolower($protocol);
    }

    public function connect()
    {
        try {
            switch ($this->protocol) {
                case 'imap':
                    $mailbox = "{{$this->server}:{$this->port}/imap/ssl}INBOX";
                    break;
                case 'pop3':
                    $mailbox = "{{$this->server}:{$this->port}/pop3/ssl}INBOX";
                    break;
                default:
                    throw new Exception("Unsupported protocol");
            }

            $this->connection = imap_open($mailbox, $this->email, $this->password);

            if (!$this->connection) {
                throw new Exception("Connection failed: " . imap_last_error());
            }

            return true;
        } catch (Exception $e) {
            error_log("Connection error: " . $e->getMessage());
            return false;
        }
    }

    public function fetchEmails($limit = 50, $folder = 'INBOX')
    {
        if (!$this->connection) {
            return [];
        }

        $emails = [];
        $totalEmails = imap_num_msg($this->connection);
        $start = max(1, $totalEmails - $limit + 1);

        for ($i = $totalEmails; $i >= $start; $i--) {
            $header = imap_headerinfo($this->connection, $i);
            $structure = imap_fetchstructure($this->connection, $i);

            $email = [
                'id' => $i,
                'subject' => $this->decodeSubject($header->subject),
                'from' => $header->from[0]->mailbox . "@" . $header->from[0]->host,
                'date' => date('Y-m-d H:i:s', strtotime($header->date)),
                'body' => $this->getEmailBody($i, $structure)
            ];

            $emails[] = $email;
        }

        return $emails;
    }

    private function decodeSubject($subject)
    {
        $subject = imap_mime_header_decode($subject);
        $decodedSubject = '';

        foreach ($subject as $obj) {
            $decodedSubject .= $obj->text;
        }

        return $decodedSubject;
    }

    private function getEmailBody($msgNumber, $structure, $partNumber = "")
    {
        if ($structure->type == 0) {
            $body = imap_fetchbody($this->connection, $msgNumber, $partNumber ? $partNumber : 1);

            switch ($structure->encoding) {
                case 3: // BASE64
                    $body = base64_decode($body);
                    break;
                case 4: // QUOTED-PRINTABLE
                    $body = quoted_printable_decode($body);
                    break;
            }

            return $body;
        }

        if ($structure->type == 1) { // multipart
            $body = "";
            foreach ($structure->parts as $partNum => $part) {
                $partNumber = $partNumber ? $partNumber . "." . ($partNum + 1) : ($partNum + 1);
                $body .= $this->getEmailBody($msgNumber, $part, $partNumber);
            }
            return $body;
        }

        return "";
    }

    public function close()
    {
        if ($this->connection) {
            imap_close($this->connection);
        }
    }
}

// Example usage HTML interface
?>
<!DOCTYPE html>
<html>

<head>
    <title>Email Extractor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .email-list {
            margin-top: 20px;
        }

        .email-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Email Extractor</h1>

        <form method="post">
            <div class="form-group">
                <label>Protocol:</label>
                <select name="protocol">
                    <option value="imap">IMAP</option>
                    <option value="pop3">POP3</option>
                </select>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Server:</label>
                <input type="text" name="server" required>
            </div>

            <div class="form-group">
                <label>Port:</label>
                <input type="number" name="port" required>
            </div>

            <button type="submit">Fetch Emails</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $extractor = new EmailExtractor(
                $_POST['email'],
                $_POST['password'],
                $_POST['server'],
                $_POST['port'],
                $_POST['protocol']
            );

            if ($extractor->connect()) {
                $emails = $extractor->fetchEmails();

                echo '<div class="email-list">';
                foreach ($emails as $email) {
                    echo '<div class="email-item">';
                    echo '<h3>' . htmlspecialchars($email['subject']) . '</h3>';
                    echo '<p><strong>From:</strong> ' . htmlspecialchars($email['from']) . '</p>';
                    echo '<p><strong>Date:</strong> ' . htmlspecialchars($email['date']) . '</p>';
                    echo '<div>' . nl2br(htmlspecialchars($email['body'])) . '</div>';
                    echo '</div>';
                }
                echo '</div>';

                $extractor->close();
            } else {
                echo '<p style="color: red;">Failed to connect to email server. Please check your credentials.</p>';
            }
        }
        ?>
    </div>
    <?php include '../tools.php'; ?>
</body>

</html>