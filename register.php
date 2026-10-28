
<?php
require_once 'db.php';

const ADMIN_REGISTRATION_CODE = 'SH-ADMIN-2026';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$name = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmpassword'] ?? '';
$role = $_POST['role'] ?? '';
$adminCode = $_POST['admin_code'] ?? '';

function showMessage($message) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>StudentHub Registration</title>
    </head>
    <body style="font-family:Arial;text-align:center;padding:40px;">
        <h2>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</h2>
        <p><a href="register.html">Return to Registration</a></p>
        <p><a href="login.html">Go to Login</a></p>
    </body>
    </html>';
    exit;
}

if (
    $name === '' ||
    $email === '' ||
    $mobile === '' ||
    $username === '' ||
    $password === '' ||
    $confirm === ''
) {
    showMessage('Please complete all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showMessage('Please enter a valid email address.');
}

if (strlen($password) < 8) {
    showMessage('Password must contain at least 8 characters.');
}

if ($password !== $confirm) {
    showMessage('Password and Confirm Password do not match.');
}

if (!in_array($role, ['student', 'admin'], true)) {
    showMessage('Please select Student or Admin.');
}

if (
    $role === 'admin' &&
    !hash_equals(ADMIN_REGISTRATION_CODE, $adminCode)
) {
    http_response_code(403);
    showMessage('Invalid Admin Registration Code.');
}

/* Check whether the email already exists */
$emailCheck = mysqli_prepare(
    $conn,
    "SELECT email FROM Stu_Register WHERE email = ? LIMIT 1"
);

if (!$emailCheck) {
    showMessage('Unable to check email. Please try again later.');
}

mysqli_stmt_bind_param($emailCheck, 's', $email);

if (!mysqli_stmt_execute($emailCheck)) {
    mysqli_stmt_close($emailCheck);
    showMessage('Unable to check email. Please try again later.');
}

mysqli_stmt_store_result($emailCheck);

if (mysqli_stmt_num_rows($emailCheck) > 0) {
    mysqli_stmt_close($emailCheck);
    showMessage('This email is already registered. Please use another email.');
}

mysqli_stmt_close($emailCheck);

/* Hash the password */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* Insert into both tables in one transaction */
mysqli_begin_transaction($conn);

try {
    $sql = "INSERT INTO Stu_Register
            (fullname, email, mobile, username, password, role)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception('Could not prepare registration query.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ssssss',
        $name,
        $email,
        $mobile,
        $username,
        $hashedPassword,
        $role
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception('Could not save registration.');
    }

    mysqli_stmt_close($stmt);

    $sql2 = "INSERT INTO Stu_Login (username, password)
             VALUES (?, ?)";

    $stmt2 = mysqli_prepare($conn, $sql2);

    if (!$stmt2) {
        throw new Exception('Could not prepare login query.');
    }

    mysqli_stmt_bind_param(
        $stmt2,
        'ss',
        $username,
        $hashedPassword
    );

    if (!mysqli_stmt_execute($stmt2)) {
        mysqli_stmt_close($stmt2);
        throw new Exception('Could not save login information.');
    }

    mysqli_stmt_close($stmt2);

    mysqli_commit($conn);

    mysqli_close($conn);

    showMessage(
        'Registration successful! Your account type is ' .
        ucfirst($role) . '. You can now log in.'
    );

} catch (Throwable $e) {
    mysqli_rollback($conn);
    mysqli_close($conn);

    http_response_code(400);

    showMessage(
        'Registration failed. The username may already exist, ' .
        'or there may be a database error.'
    );
}
?>
