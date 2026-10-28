
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (
        empty($_SESSION['username']) ||
        empty($_SESSION['role'])
    ) {
        header('Location: login.html');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();

    if (strtolower((string)$_SESSION['role']) !== 'admin') {
        http_response_code(403);
        exit(
            'Access denied. Only administrators can perform this action.'
        );
    }
}

function logAction(
    mysqli $conn,
    string $username,
    string $action,
    string $details = ''
): void {
    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO audit_logs (username, action, details)
         VALUES (?, ?, ?)'
    );

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            'sss',
            $username,
            $action,
            $details
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
?>
