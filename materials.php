
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - StudentHub</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            color: #1e293b;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
        }

        h1 {
            color: #0f766e;
        }

        .material {
            padding: 18px;
            margin: 15px 0;
            background: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 8px;
        }

        a {
            color: #0f766e;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 18px;
            background: #0d9488;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>Study Materials</h1>

    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    <p>Access your study resources below.</p>

    <div class="material">
        <h3>Data Structures and Algorithms</h3>
        <p>Notes and practice resources for DSA.</p>
    </div>

    <div class="material">
        <h3>Database Management System</h3>
        <p>Study resources for DBMS and SQL.</p>
    </div>

    <div class="material">
        <h3>Computer Networks</h3>
        <p>Notes for networking concepts and protocols.</p>
    </div>

    <p>No downloadable files have been uploaded yet.</p>

    <a class="back" href="student_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
