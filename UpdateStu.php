
<?php
require_once 'db.php';
require_once 'auth.php';

requireAdmin();

$message = '';
$error = '';

$username = trim($_GET['username'] ?? $_POST['username'] ?? '');

if ($username === '') {
    http_response_code(400);
    exit('Student username is required.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    if ($fullname === '' || $email === '' || $mobile === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Stu_Register
             SET fullname = ?, email = ?, mobile = ?
             WHERE username = ? AND LOWER(role) = 'student'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'ssss',
            $fullname,
            $email,
            $mobile,
            $username
        );

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) >= 0) {
                logAction(
                    $conn,
                    $_SESSION['username'],
                    'UPDATE_STUDENT',
                    'Updated student: ' . $username
                );

                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                header('Location: show_students.php');
                exit;
            }
        } else {
            $error = 'Update failed. Check the email and database constraints.';
        }

        mysqli_stmt_close($stmt);
    }
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT fullname, email, mobile, username
     FROM Stu_Register
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Update Student - StudentHub</title>
</head>
<body>
<h1>Update Student Details</h1>

<?php if ($error !== ''): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="UpdateStu.php" method="post">
<input type="hidden" name="username"
value="<?= htmlspecialchars($student['username']) ?>">

<p>
<label>Username</label>
<input value="<?= htmlspecialchars($student['username']) ?>" readonly>
</p>

<p>
<label>Full Name</label>
<input name="fullname" required
value="<?= htmlspecialchars($_POST['fullname'] ?? $student['fullname']) ?>">
</p>

<p>
<label>Email</label>
<input type="email" name="email" required
value="<?= htmlspecialchars($_POST['email'] ?? $student['email']) ?>">
</p>

<p>
<label>Mobile</label>
<input name="mobile" required
value="<?= htmlspecialchars($_POST['mobile'] ?? $student['mobile']) ?>">
</p>

<button type="submit">Update Student</button>
<a href="show_students.php">Cancel</a>
</form>
</body>
</html>
<?php mysqli_close($conn); ?>
