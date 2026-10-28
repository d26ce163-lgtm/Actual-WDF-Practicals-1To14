<?php
session_start();
require_once 'db.php';

if (
    !isset($_SESSION['username']) ||
    strtolower($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: admin_login.php');
    exit;
}

$sql = "SELECT r.registration_id, r.username, r.registered_at,
               e.event_name, e.event_date
        FROM Event_Registrations r
        INNER JOIN Events e ON e.event_id = r.event_id
        ORDER BY e.event_date DESC, r.registered_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Unable to load event participants.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Participants - StudentHub</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f5f9; padding: 25px; }
        .box { max-width: 1000px; margin: auto; background: white; padding: 25px; border-radius: 10px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #1e3a8a; color: white; }
    </style>
</head>
<body>
<div class="box">
    <h1>Event Participants</h1>
    <p>Admin: <?= htmlspecialchars($_SESSION['username']) ?></p>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Registration ID</th>
                <th>Student Username</th>
                <th>Event Name</th>
                <th>Event Date</th>
                <th>Registered At</th>
            </tr>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int)$row['registration_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['event_name']) ?></td>
                        <td><?= htmlspecialchars($row['event_date']) ?></td>
                        <td><?= htmlspecialchars($row['registered_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No students have registered for events yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
</div>
</body>
</html>
<?php
mysqli_free_result($result);
mysqli_close($conn);
?>