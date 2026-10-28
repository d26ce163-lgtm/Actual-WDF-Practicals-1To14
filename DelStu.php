
<?php
require_once 'db.php';
require_once 'auth.php';

requireAdmin();

$username = trim($_POST['username'] ?? $_GET['username'] ?? '');

if ($username === '') {
    http_response_code(400);
    exit('Student username is required.');
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT fullname FROM Stu_Register
     WHERE username = ? AND LOWER(role) = 'student'"
);

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    http_response_code(404);
    exit('Student not found.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['confirm'] ?? '') !== 'yes') {
        header('Location: show_students.php');
        exit;
    }

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM Stu_Register
         WHERE username = ? AND LOWER(role) = 'student'"
    );

    mysqli_stmt_bind_param($stmt, 's', $username);

    if (
        mysqli_stmt_execute($stmt) &&
        mysqli_stmt_affected_rows($stmt) === 1
    ) {
        logAction(
            $conn,
            $_SESSION['username'],
            'DELETE_STUDENT',
            'Deleted student: ' . $username
        );

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header('Location: show_students.php');
        exit;
    }

    $message = 'Delete failed. The student may be linked to other records.';
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Delete Student - StudentHub</title>
</head>
<body>
<h1>Delete Student</h1>

<?php if ($message !== ''): ?>
<p style="color:red"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<p>
Are you sure you want to delete
<strong><?= htmlspecialchars($student['fullname']) ?></strong>
(<?= htmlspecialchars($username) ?>)?
</p>

<form method="post" action="DelStu.php">
<input type="hidden" name="username"
value="<?= htmlspecialchars($username) ?>">
<button type="submit" name="confirm" value="yes">
Yes, Delete
</button>
<button type="submit" name="confirm" value="no">
Cancel
</button>
</form>

</body>
</html>
<?php mysqli_close($conn); ?>
