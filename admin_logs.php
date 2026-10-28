<?php
require_once 'db.php';
require_once 'auth.php';
requireAdmin();
$result = mysqli_query($conn, 'SELECT username, action, details, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 200');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Audit Logs - StudentHub</title><link rel="stylesheet" href="admin.css"></head><body><div class="layout"><aside class="sidebar"><h2>StudentHub</h2><a href="admin_dashboard.php">Dashboard</a><a href="show_students.php">Manage Students</a><a href="events.php">View Events</a><a class="active" href="admin_logs.php">Audit Logs</a><a href="logout.php">Logout</a></aside><main class="main"><header class="topbar"><div><h1>Audit Logs</h1><p>Recent administrative activity.</p></div></header><section class="panel"><div class="table-wrap"><table><thead><tr><th>Username</th><th>Action</th><th>Details</th><th>Date and Time</th></tr></thead><tbody><?php if ($result && mysqli_num_rows($result) > 0): while ($row = mysqli_fetch_assoc($result)): ?><tr><td><?= htmlspecialchars($row['username']) ?></td><td><?= htmlspecialchars($row['action']) ?></td><td><?= htmlspecialchars($row['details'] ?? '') ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr><?php endwhile; else: ?><tr><td colspan="4">No audit records yet.</td></tr><?php endif; ?></tbody></table></div><p><a href="admin_dashboard.php">Back to Dashboard</a></p></section></main></div></body></html>
<?php if ($result) mysqli_free_result($result); mysqli_close($conn); ?>
