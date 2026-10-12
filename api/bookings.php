<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../config/database.php';

function respond(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $pdo->query(
        "SELECT id, name, phone, email, service, notes,
                DATE_FORMAT(booking_date, '%Y-%m-%d') AS date,
                booking_time AS time, status,
                created_at AS createdAt
         FROM bookings ORDER BY booking_date, booking_time"
    );
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['id'] = (int)$row['id'];
        $row['email'] = $row['email'] ?? '';
        $row['notes'] = $row['notes'] ?? '';
    }
    unset($row);
    respond(200, ['success' => true, 'data' => $rows]);
}

if ($method !== 'POST') {
    header('Allow: GET, POST');
    respond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) respond(400, ['success' => false, 'message' => 'Invalid JSON request.']);

$name = trim((string)($input['name'] ?? ''));
$phone = trim((string)($input['phone'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$service = trim((string)($input['service'] ?? ''));
$notes = trim((string)($input['notes'] ?? ''));
$date = trim((string)($input['date'] ?? ''));
$time = trim((string)($input['time'] ?? ''));

$allowedServices = ['Consultation','Haircut','Massage','Dental Checkup','Facial','Manicure','Physical Therapy','Other'];
$allowedTimes = ['08:00 AM','09:00 AM','10:00 AM','11:00 AM','12:00 PM','01:00 PM','02:00 PM','03:00 PM','04:00 PM','05:00 PM','06:00 PM','07:00 PM'];
$dateObject = DateTime::createFromFormat('!Y-m-d', $date);
if ($name === '' || $phone === '' || $service === '' || !$dateObject || $dateObject->format('Y-m-d') !== $date || !in_array($service, $allowedServices, true) || !in_array($time, $allowedTimes, true)) {
    respond(422, ['success' => false, 'message' => 'Please provide valid name, phone, service, date and time.']);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(422, ['success' => false, 'message' => 'Please enter a valid email address.']);
}
if ($date < date('Y-m-d')) respond(422, ['success' => false, 'message' => 'You cannot book a date in the past.']);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO bookings (name, phone, email, service, notes, booking_date, booking_time)
         VALUES (:name, :phone, :email, :service, :notes, :booking_date, :booking_time)'
    );
    $stmt->execute([
        ':name' => $name, ':phone' => $phone,
        ':email' => $email !== '' ? $email : null,
        ':service' => $service, ':notes' => $notes !== '' ? $notes : null,
        ':booking_date' => $date, ':booking_time' => $time,
    ]);
    respond(201, ['success' => true, 'message' => 'Booking saved.', 'id' => (int)$pdo->lastInsertId()]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        respond(409, ['success' => false, 'message' => 'That time slot has already been booked. Please choose another time.']);
    }
    respond(500, ['success' => false, 'message' => 'Could not save booking. Please try again.']);
}
