<?php
header('Content-Type: application/json');

function extractDomain($url)
{
    // Remove protocol (http, https, ftp, etc.)
    $domain = preg_replace('(^https?://)', '', $url);

    // Remove path, query string and fragment
    $domain = strtok($domain, '/');

    // Remove www. if present
    $domain = preg_replace('/^www\./', '', $domain);

    // Trim whitespace
    $domain = trim($domain);

    return $domain;
}

try {
    if (!isset($_POST['domains'])) {
        throw new Exception('No domains provided');
    }

    $input = trim($_POST['domains']);
    if (empty($input)) {
        throw new Exception('Empty input provided');
    }

    // Split input into lines
    $lines = preg_split('/\r\n|\r|\n/', $input);

    // Process each line
    $extractedDomains = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            $domain = extractDomain($line);
            if (!empty($domain)) {
                $extractedDomains[] = $domain;
            }
        }
    }

    // Remove duplicates and empty values
    $extractedDomains = array_unique(array_filter($extractedDomains));

    echo json_encode([
        'success' => true,
        'domains' => array_values($extractedDomains)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
