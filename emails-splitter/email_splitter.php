<?php
header('Content-Type: application/json');

function splitEmails($filePath, $splitSize)
{
    // Validate input
    if (!file_exists($filePath)) {
        return ['error' => 'File not found'];
    }

    // Create uploads and chunks directories if they don't exist
    $uploadsDir = 'uploads/';
    $chunksDir = 'chunks/';

    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    if (!is_dir($chunksDir)) mkdir($chunksDir, 0755, true);

    // Generate unique session ID for this split operation
    $sessionId = uniqid('split_');

    // Read file
    $emails = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Validate emails
    $validEmails = array_filter($emails, function ($email) {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    });

    // Split into chunks
    $chunks = array_chunk($validEmails, $splitSize);

    // Generate chunk files
    $chunkFiles = [];
    foreach ($chunks as $index => $chunk) {
        $chunkFileName = $chunksDir . $sessionId . '_chunk_' . ($index + 1) . '.txt';
        file_put_contents($chunkFileName, implode("\n", $chunk));
        $chunkFiles[] = $chunkFileName;
    }

    // Create ZIP of chunks
    $zipFileName = $uploadsDir . $sessionId . '_email_chunks.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($chunkFiles as $chunkFile) {
            $zip->addFile($chunkFile, basename($chunkFile));
        }
        $zip->close();
    }

    // Cleanup original uploaded file
    unlink($filePath);

    return [
        'total_emails' => count($validEmails),
        'chunk_count' => count($chunks),
        'chunks' => array_map('basename', $chunkFiles),
        'zip_file' => basename($zipFileName),
        'session_id' => $sessionId
    ];
}

// Handle file upload and processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['email_file'])) {
            throw new Exception('No file uploaded');
        }

        $file = $_FILES['email_file'];
        $splitSize = isset($_POST['split_size']) ? intval($_POST['split_size']) : 1000;

        // Validate file
        $allowedTypes = ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only txt and csv allowed.');
        }

        // Generate unique filename
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $uploadPath = $uploadDir . uniqid('emails_') . '_' . basename($file['name']);

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $result = splitEmails($uploadPath, $splitSize);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            throw new Exception('File upload failed');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// File download endpoint
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    $path = '';

    // Determine file path based on type
    if (strpos($file, 'chunk_') !== false) {
        $path = 'chunks/' . $file;
    } elseif (strpos($file, 'email_chunks.zip') !== false) {
        $path = 'uploads/' . $file;
    }

    if (file_exists($path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    } else {
        die('File not found');
    }
}
