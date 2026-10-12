<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../config/database.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'A valid booking ID is required.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id AND status = 'upcoming'");
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Upcoming booking not found.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Booking cancelled.']);
