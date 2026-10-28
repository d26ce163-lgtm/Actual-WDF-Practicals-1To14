<?php
require_once 'db.php';
require_once 'auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.html'); exit; }
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$requestedRole = $_POST['login_role'] ?? 'student';
if ($username === '' || $password === '' || !in_array($requestedRole, ['student', 'admin'], true)) {
    exit('Enter username, password, and a valid login type. <a href="login.html">Go back</a>');
}
$stmt = mysqli_prepare($conn, 'SELECT username, password, role FROM Stu_Register WHERE username = ? LIMIT 1');
if (!$stmt) exit('Database query error. Check your database columns.');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401); exit('Incorrect username or password. <a href="login.html">Try again</a>');
}
$actualRole = $user['role'] ?? 'student';
if ($actualRole !== $requestedRole) {
    http_response_code(403); exit('This account is not registered as ' . htmlspecialchars($requestedRole) . '. Choose the correct login type. <a href="login.html">Go back</a>');
}
session_regenerate_id(true);
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $actualRole;
if ($actualRole === 'admin') {
    logAction($conn, $user['username'], 'ADMIN_LOGIN', 'Admin logged in');
    header('Location: admin_dashboard.php');
} else {
    header('Location: student_dashboard.php');
}
mysqli_close($conn);
exit;
?>