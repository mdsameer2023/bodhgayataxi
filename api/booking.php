<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/form_backend.php';
form_post_only();

if (form_field('website', 200, false) !== '') {
    form_response(422, 'Please refresh the page and submit the form again.');
}

$name = form_field('full-name', 120);
$email = form_email();
$vehicle = form_field('package-type', 50);
$passengers = form_field('passengers', 2);
$start = form_field('start-dest', 160);
$end = form_field('end-dest', 160);
$date = form_field('ride-date', 10);
$time = form_field('ride-time', 5);

if (!in_array($vehicle, ['ertiga', 'swift-dzire', 'innova_crysta', 'scorpip', 'toyota_etios', 'standard', 'business', 'economy', 'vip-spacial', 'comfort'], true)
    || !in_array($passengers, ['1', '2', '3', '4', '5'], true)) {
    form_response(422, 'Please select a valid vehicle and passenger count.');
}

$rideDate = DateTimeImmutable::createFromFormat('!d/m/Y', $date, new DateTimeZone('Asia/Kolkata'));
if (!$rideDate || $rideDate->format('d/m/Y') !== $date || $rideDate < new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata'))
    || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
    form_response(422, 'Please choose a valid future ride date and time.');
}

form_save('bookings', [
    'name' => $name,
    'email' => $email,
    'vehicle' => $vehicle,
    'passengers' => (int) $passengers,
    'start_destination' => $start,
    'end_destination' => $end,
    'ride_date' => $date,
    'ride_time' => $time,
    'status' => 'new',
]);
form_response(200, 'Your booking enquiry has been received. We will contact you soon.');
