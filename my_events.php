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

$username = $_SESSION['username'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT e.event_name, e.organization, e.event_date,
            e.venue, r.registered_at
     FROM Event_Registrations r
     INNER JOIN Events e ON e.event_id = r.event_id
     WHERE r.username = ?
     ORDER BY r.registered_at DESC"
);

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - StudentHub</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f5f9; padding: 25px; }
        .box { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #1e3a8a; color: white; }
        .success { color: #15803d; }
        .warning { color: #b45309; }
        .table-wrap { overflow-x: auto; }
    </style>
</head>
<body>
<div class="box">
    <h1>My Registered Events</h1>

    <?php if ($status === 'success'): ?>
        <p class="success">Successfully registered for the event!</p>
    <?php elseif ($status === 'exists'): ?>
        <p class="warning">You are already registered for that event.</p>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Event</th>
                <th>Organization</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Registered At</th>
            </tr>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['event_name']) ?></td>
                        <td><?= htmlspecialchars($event['organization']) ?></td>
                        <td><?= htmlspecialchars($event['event_date']) ?></td>
                        <td><?= htmlspecialchars($event['venue']) ?></td>
                        <td><?= htmlspecialchars($event['registered_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">You have not registered for any events yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <p><a href="events.php">Browse Events</a></p>
    <p><a href="student_dashboard.php">Student Dashboard</a></p>
</div>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>