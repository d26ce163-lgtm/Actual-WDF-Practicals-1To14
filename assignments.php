
<?php
session_start();

if (
    !isset($_SESSION['username']) ||
    strtolower($_SESSION['role'] ?? '') !== 'student'
) {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments - StudentHub</title>
</head>
<body>
    <h1>Student Assignments</h1>

    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>

    <p>No assignments have been added yet.</p>

    <a href="student_dashboard.php">Back to Dashboard</a>
</body>
</html>
