<?php
declare(strict_types=1);

// Development server router: never serve secrets or PHP include files directly.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (preg_match('~^/(?:\.env(?:\..*)?|\.git(?:/|$)|includes(?:/|$))~', $path)) {
    http_response_code(404);
    echo 'Not found.';
    return true;
}

return false;
