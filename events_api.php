
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

mysqli_set_charset($conn, 'utf8mb4');

function sendJson($success, $message, $data = null, $status = 200) {
    http_response_code($status);

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);

    exit;
}

/* API access: admin only */
if (
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'admin'
) {
    sendJson(false, 'Please log in as an administrator.', null, 401);
}

/* Read JSON request body */
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = [];
}

$method = $_SERVER['REQUEST_METHOD'];

/* GET: LIST AND SEARCH EVENTS */
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');

    if ($search !== '') {
        $stmt = $conn->prepare(
            "SELECT event_id, event_name, organization, event_date, fees, venue
             FROM Events
             WHERE event_name LIKE ?
                OR organization LIKE ?
                OR venue LIKE ?
             ORDER BY event_id DESC"
        );

        if (!$stmt) {
            sendJson(false, 'Database query failed.', null, 500);
        }

        $term = '%' . $search . '%';
        $stmt->bind_param('sss', $term, $term, $term);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query(
            "SELECT event_id, event_name, organization, event_date, fees, venue
             FROM Events
             ORDER BY event_id DESC"
        );

        if (!$result) {
            sendJson(false, 'Could not load events.', null, 500);
        }
    }

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $row['event_id'] = (int)$row['event_id'];
        $row['fees'] = (float)$row['fees'];
        $events[] = $row;
    }

    if (isset($stmt)) {
        $stmt->close();
    }

    sendJson(true, 'Events loaded successfully.', $events);
}

/* Other operations must use POST */
if ($method !== 'POST') {
    header('Allow: GET, POST');
    sendJson(false, 'Method not allowed.', null, 405);
}

$action = $input['action'] ?? '';

/* ADD EVENT */
if ($action === 'add') {
    $eventName = trim($input['event_name'] ?? '');
    $organization = trim($input['organization'] ?? '');
    $eventDate = trim($input['event_date'] ?? '');
    $fees = $input['fees'] ?? '';
    $venue = trim($input['venue'] ?? '');

    if (
        $eventName === '' || $organization === '' ||
        $eventDate === '' || $fees === '' || $venue === ''
    ) {
        sendJson(false, 'Please fill in all fields.', null, 422);
    }

    if (
        strlen($eventName) > 150 ||
        strlen($organization) > 150 ||
        strlen($venue) > 200
    ) {
        sendJson(false, 'One or more text fields are too long.', null, 422);
    }

    if (!is_numeric($fees) || !is_finite((float)$fees) || (float)$fees < 0) {
        sendJson(false, 'Fees must be a valid non-negative number.', null, 422);
    }

    $dateObject = DateTime::createFromFormat('!Y-m-d', $eventDate);

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $eventDate
    ) {
        sendJson(false, 'Please enter a valid event date.', null, 422);
    }

    $feeValue = (float)$fees;

    $stmt = $conn->prepare(
        "INSERT INTO Events
         (event_name, organization, event_date, fees, venue)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        sendJson(false, 'Database query failed.', null, 500);
    }

    $stmt->bind_param(
        'sssds',
        $eventName,
        $organization,
        $eventDate,
        $feeValue,
        $venue
    );

    if (!$stmt->execute()) {
        $stmt->close();
        sendJson(false, 'Could not add event.', null, 500);
    }

    $newId = $conn->insert_id;
    $stmt->close();

    sendJson(true, 'Event added successfully.', ['event_id' => $newId], 201);
}

/* UPDATE EVENT */
if ($action === 'update') {
    $eventId = filter_var(
        $input['event_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $eventName = trim($input['event_name'] ?? '');
    $organization = trim($input['organization'] ?? '');
    $eventDate = trim($input['event_date'] ?? '');
    $fees = $input['fees'] ?? '';
    $venue = trim($input['venue'] ?? '');

    if (
        !$eventId || $eventName === '' || $organization === '' ||
        $eventDate === '' || $fees === '' || $venue === ''
    ) {
        sendJson(false, 'Please provide all required fields.', null, 422);
    }

    if (
        strlen($eventName) > 150 ||
        strlen($organization) > 150 ||
        strlen($venue) > 200
    ) {
        sendJson(false, 'One or more text fields are too long.', null, 422);
    }

    if (!is_numeric($fees) || !is_finite((float)$fees) || (float)$fees < 0) {
        sendJson(false, 'Fees must be a valid non-negative number.', null, 422);
    }

    $dateObject = DateTime::createFromFormat('!Y-m-d', $eventDate);

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $eventDate
    ) {
        sendJson(false, 'Please enter a valid event date.', null, 422);
    }

    $feeValue = (float)$fees;

    $stmt = $conn->prepare(
        "UPDATE Events
         SET event_name = ?, organization = ?, event_date = ?,
             fees = ?, venue = ?
         WHERE event_id = ?"
    );

    if (!$stmt) {
        sendJson(false, 'Database query failed.', null, 500);
    }

    $stmt->bind_param(
        'sssdsi',
        $eventName,
        $organization,
        $eventDate,
        $feeValue,
        $venue,
        $eventId
    );

    if (!$stmt->execute()) {
        $stmt->close();
        sendJson(false, 'Could not update event.', null, 500);
    }

    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        $check = $conn->prepare(
            "SELECT event_id FROM Events WHERE event_id = ?"
        );
        $check->bind_param('i', $eventId);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$exists) {
            sendJson(false, 'Event not found.', null, 404);
        }
    }

    sendJson(true, 'Event updated successfully.', ['event_id' => $eventId]);
}

/* DELETE EVENT */
if ($action === 'delete') {
    $eventId = filter_var(
        $input['event_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$eventId) {
        sendJson(false, 'A valid event ID is required.', null, 422);
    }

    $stmt = $conn->prepare(
        "DELETE FROM Events WHERE event_id = ?"
    );

    if (!$stmt) {
        sendJson(false, 'Database query failed.', null, 500);
    }

    $stmt->bind_param('i', $eventId);

    if (!$stmt->execute()) {
        $stmt->close();
        sendJson(false, 'Could not delete event.', null, 500);
    }

    $deleted = $stmt->affected_rows;
    $stmt->close();

    if ($deleted === 0) {
        sendJson(false, 'Event not found.', null, 404);
    }

    sendJson(true, 'Event deleted successfully.', ['event_id' => $eventId]);
}

/* Unknown action */
sendJson(false, 'Unknown action. Use add, update, or delete.', null, 400);
