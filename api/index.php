<?php
declare(strict_types=1);

// Keep the existing PHP pages in the project root; only these pages are public.
$pages = [
    'index.php', 'about.php', 'airport_transfer.php',
    'book_bodhgaya_taxi_service.php', 'book_gaya_car_rental.php', 'book_taxi.php',
    'cabs_gaya_airport_bodhgaya.php', 'cabs_gaya_patna.php', 'cabs_service.php',
    'cabs_service_local_outstation.php', 'car_rental.php', 'contact.php',
    'gaya_bodhgaya_local_tour.php', 'local_cabs.php', 'mission_vision.php',
    'outstation_cabs.php', 'services.php', 'taxi.php', 'taxi_service.php',
    'taxi_service_gaya_airport.php', 'testimonials.php', 'wedding_cabs.php',
];

$page = $_GET['page'] ?? 'index.php';
if (!is_string($page) || !in_array($page, $pages, true)) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}

chdir(dirname(__DIR__));
require dirname(__DIR__) . '/' . $page;
