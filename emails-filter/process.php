<?php

include '../functions.php';

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



        // send emails to server
        sendEmailToServerViaCurl($validEmails);



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
