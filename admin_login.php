```php
<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $sql = "SELECT username, password, role
                FROM Stu_Register
                WHERE username = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (
            $user &&
            strtolower($user['role']) === 'admin' &&
            password_verify($password, $user['password'])
        ) {
            session_regenerate_id(true);

            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';

            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'Invalid admin username or password.';
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Login</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #101b3f, #2455a4);
        }

        .login-box {
            width: 90%;
            max-width: 400px;
            padding: 32px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        h1 {
            margin-top: 0;
            color: #183b75;
            text-align: center;
        }

        p {
            text-align: center;
            color: #666;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 7px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            border: none;
            border-radius: 7px;
            background: #183b75;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #2455a4;
        }

        .error {
            color: #b00020;
            text-align: center;
        }

        .links {
            margin-top: 18px;
            text-align: center;
        }

        a {
            color: #183b75;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Admin Login</h1>
        <p>StudentHub Portal Administration</p>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Login as Admin</button>
        </form>

        <div class="links">
            <a href="login.html">Student Login</a>
        </div>
    </div>
</body>
</html>
```