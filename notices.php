
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
    <title>Notices - StudentHub</title>

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

        .notice {
            background: #f0fdfa;
            border-left: 5px solid #0d9488;
            padding: 18px;
            margin: 15px 0;
            border-radius: 6px;
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
    <h1>Student Notices</h1>

    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>

    <div class="notice">
        <h3>Welcome to StudentHub</h3>
        <p>Use the portal to access study materials and event information.</p>
    </div>

    <div class="notice">
        <h3>Check Upcoming Events</h3>
        <p>Visit the Events section to view available student activities.</p>
        <a href="events.php">View Events</a>
    </div>

    <p>These are sample notices. Official notices can be added later.</p>

    <a class="back" href="student_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
