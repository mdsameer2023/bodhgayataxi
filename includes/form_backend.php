<?php
declare(strict_types=1);

function form_response(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function form_post_only(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        form_response(405, 'Only POST requests are allowed.');
    }
    if (strlen(file_get_contents('php://input') ?: '') > 16384) {
        form_response(413, 'Form data is too large.');
    }
}

function form_field(string $key, int $maxLength, bool $required = true): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        form_response(422, 'Invalid form data.');
    }
    $value = trim($value);
    if (($required && $value === '') || strlen($value) > $maxLength || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
        form_response(422, 'Please check the ' . str_replace('-', ' ', $key) . ' field.');
    }
    return $value;
}

function form_email(): string
{
    $email = form_field('email', 254);
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        form_response(422, 'Please enter a valid email address.');
    }
    return $email;
}

function form_load_local_env(): void
{
    $envFile = dirname(__DIR__) . '/.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
        return;
    }

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
            continue;
        }
        $key = $matches[1];
        if (getenv($key) !== false) {
            continue;
        }
        $value = trim($matches[2]);
        if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
    }
}

function form_save(string $collection, array $document): void
{
    form_load_local_env();
    $uri = getenv('MONGODB_URI') ?: getenv('MONGODB_URL');
    $database = getenv('MONGODB_DB');
    if (!$uri || !$database || !preg_match('/^[A-Za-z0-9_-]+$/', $database) || !extension_loaded('mongodb')) {
        error_log('MongoDB configuration is missing or the extension is unavailable.');
        form_response(503, 'Service is temporarily unavailable. Please call us instead.');
    }

    try {
        $manager = new MongoDB\Driver\Manager($uri, [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS' => 5000,
        ]);
        $bulk = new MongoDB\Driver\BulkWrite();
        $document['created_at'] = new MongoDB\BSON\UTCDateTime();
        $bulk->insert($document);
        $manager->executeBulkWrite($database . '.' . $collection, $bulk, [
            'writeConcern' => new MongoDB\Driver\WriteConcern(1, 5000),
        ]);
    } catch (Throwable $error) {
        error_log('MongoDB write failed: ' . $error->getMessage());
        form_response(503, 'We could not save your request. Please try again or call us.');
    }
}
