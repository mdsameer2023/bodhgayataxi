<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/form_backend.php';
form_post_only();

if (form_field('website', 200, false) !== '') {
    form_response(422, 'Please refresh the page and submit the form again.');
}

$firstName = form_field('firstname', 80);
$lastName = form_field('lastname', 80);
$email = form_email();
$phone = form_field('phone', 25);
$message = form_field('message', 3000);

if (!preg_match('/^\+?[0-9()\s-]{7,25}$/', $phone)) {
    form_response(422, 'Please enter a valid phone number.');
}

form_save('contacts', [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'phone' => $phone,
    'message' => $message,
    'status' => 'new',
]);
form_response(200, 'Your message has been received. We will contact you soon.');
