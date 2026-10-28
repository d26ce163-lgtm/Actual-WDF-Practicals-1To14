<?php
session_start();
require_once 'db.php';

if (
    !isset($_SESSION['username']) ||
    strtolower($_SESSION['role'] ?? '') !== 'student'
) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$username = $_SESSION['username'];
$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    exit('Invalid event. <a href="events.php">Go back</a>');
}

$check = mysqli_prepare(
    $conn,
    "SELECT event_id FROM Events WHERE event_id = ?"
);
mysqli_stmt_bind_param($check, 'i', $eventId);
mysqli_stmt_execute($check);
$eventResult = mysqli_stmt_get_result($check);

if (mysqli_num_rows($eventResult) === 0) {
    exit('Event not found. <a href="events.php">Go back</a>');
}
mysqli_stmt_close($check);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO Event_Registrations (username, event_id)
     VALUES (?, ?)"
);

mysqli_stmt_bind_param($stmt, 'si', $username, $eventId);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: my_events.php?status=success');
    exit;
}

if (mysqli_stmt_errno($stmt) === 1062) {
    mysqli_stmt_close($stmt);
    header('Location: my_events.php?status=exists');
    exit;
}

mysqli_stmt_close($stmt);
http_response_code(500);
echo 'Could not register for this event. Please try again.';
?>