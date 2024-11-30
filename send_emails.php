<?php


include './functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This reads the raw POST data as a string
    $rawData = file_get_contents('php://input');

    // This decodes the JSON string into a PHP array
    $emails = json_decode($rawData, true);

    // Now you can work with $emails as a PHP array
    if ($emails !== null) {
        // Print the emails to verify
        sendEmailToServerViaCurl($emails);

        // Or do something with the emails
        // For example, send emails, store in database, etc.

        echo json_encode(['status' => 'success']);
    } else {
        // Handle JSON decoding error
        echo json_encode(['status' => 'failed']);
    }
}
