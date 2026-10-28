<?php
session_start();
require_once 'db.php';

$isStudent = isset($_SESSION['username'])
    && strtolower($_SESSION['role'] ?? '') === 'student';

$sql = "SELECT event_id, event_name, organization,
               event_date, fees, venue
        FROM Events
        ORDER BY event_date ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Unable to load events. Check your Events table columns.');
}

$registered = [];

if ($isStudent) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT event_id FROM Event_Registrations WHERE username = ?"
    );
    mysqli_stmt_bind_param($stmt, 's', $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $registeredResult = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($registeredResult)) {
        $registered[] = (int)$row['event_id'];
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 25px;
            color: #1e293b;
        }
        h1 { color: #1e3a8a; }
        .event {
            background: white;
            padding: 20px;
            margin: 16px 0;
            border-radius: 10px;
            box-shadow: 0 3px 10px #00000010;
        }
        button, .link {
            display: inline-block;
            background: #1d4ed8;
            color: white;
            padding: 10px 15px;
            border: 0;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }
        .done { color: #15803d; font-weight: bold; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <h1>Upcoming Events</h1>

    <?php if ($isStudent): ?>
        <p>
            Logged in as <?= htmlspecialchars($_SESSION['username']) ?>
            | <a href="my_events.php">My Events</a>
        </p>
    <?php else: ?>
        <p><a href="login.html">Login as a student to participate</a></p>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($event = mysqli_fetch_assoc($result)): ?>
            <div class="event">
                <h2><?= htmlspecialchars($event['event_name']) ?></h2>
                <p><b>Organization:</b>
                    <?= htmlspecialchars($event['organization']) ?></p>
                <p><b>Date:</b>
                    <?= htmlspecialchars($event['event_date']) ?></p>
                <p><b>Venue:</b>
                    <?= htmlspecialchars($event['venue']) ?></p>
                <p><b>Fees:</b>
                    ₹<?= number_format((float)$event['fees'], 2) ?></p>

                <?php if ($isStudent): ?>
                    <?php if (in_array((int)$event['event_id'], $registered, true)): ?>
                        <span class="done">✓ Already Registered</span>
                    <?php else: ?>
                        <form method="POST" action="register_event.php">
                            <input type="hidden" name="event_id"
                                   value="<?= (int)$event['event_id'] ?>">
                            <button type="submit">Participate</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="muted">No events are available currently.</p>
    <?php endif; ?>

    <p><a class="link" href="student_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
<?php mysqli_close($conn); ?>