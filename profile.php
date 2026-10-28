
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
    "SELECT username, fullname, email, mobile
     FROM Stu_Register
     WHERE username = ?"
);

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - StudentHub</title>
</head>
<body>
    <h1>My Profile</h1>

    <?php if ($student) { ?>
        <p><b>Username:</b>
            <?= htmlspecialchars($student['username'] ?? '') ?></p>

        <p><b>Full Name:</b>
            <?= htmlspecialchars($student['fullname'] ?? '') ?></p>

        <p><b>Email:</b>
            <?= htmlspecialchars($student['email'] ?? '') ?></p>

        <p><b>Mobile:</b>
            <?= htmlspecialchars($student['mobile'] ?? '') ?></p>
    <?php } else { ?>
        <p>Student profile not found.</p>
    <?php } ?>

    <a href="student_dashboard.php">Back to Dashboard</a>
</body>
</html>
