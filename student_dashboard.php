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
$safeUsername = htmlspecialchars($username);

$registeredCount = 0;

$countStmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM Event_Registrations
     WHERE username = ?"
);

if ($countStmt) {
    mysqli_stmt_bind_param($countStmt, 's', $username);
    mysqli_stmt_execute($countStmt);

    $countResult = mysqli_stmt_get_result($countStmt);

    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $registeredCount = (int)($countRow['total'] ?? 0);
    }

    mysqli_stmt_close($countStmt);
}

$upcomingEvents = mysqli_query(
    $conn,
    "SELECT event_id, event_name, event_date, venue
     FROM Events
     WHERE event_date >= CURDATE()
     ORDER BY event_date ASC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard - StudentHub</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #0f766e;
            color: white;
            padding: 22px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        header h1 {
            font-size: 25px;
        }

        header p {
            margin-top: 6px;
            color: #ccfbf1;
        }

        .logout {
            background: #ef4444;
            color: white;
            text-decoration: none;
            padding: 10px 17px;
            border-radius: 7px;
        }

        .container {
            width: 88%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .welcome {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px #0000000d;
        }

        .welcome h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #115e59;
        }

        .welcome p {
            line-height: 1.6;
            color: #64748b;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border-left: 5px solid #0d9488;
            box-shadow: 0 3px 12px #0000000d;
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .stat-card p {
            font-size: 32px;
            font-weight: bold;
            color: #115e59;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000d;
            margin-bottom: 25px;
        }

        .section h2 {
            margin-bottom: 20px;
            font-size: 21px;
        }

        .links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 15px;
        }

        .links a {
            display: block;
            background: #f0fdfa;
            color: #0f766e;
            text-decoration: none;
            padding: 20px;
            border-radius: 9px;
            border: 1px solid #ccfbf1;
            font-weight: bold;
            transition: 0.2s;
        }

        .links a:hover {
            background: #ccfbf1;
            transform: translateY(-2px);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f0fdfa;
            color: #115e59;
        }

        .empty {
            color: #64748b;
            line-height: 1.6;
        }

        .browse-button {
            display: inline-block;
            margin-top: 18px;
            background: #0d9488;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 7px;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            .container {
                width: 92%;
            }

            header h1 {
                font-size: 21px;
            }

            .welcome h2 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<header>
    <div>
        <h1>StudentHub Portal</h1>
        <p>Student Dashboard</p>
    </div>

    <a class="logout" href="logout.php">Logout</a>
</header>

<main class="container">

    <section class="welcome">
        <h2>Welcome, <?php echo $safeUsername; ?>!</h2>
        <p>
            Welcome to your StudentHub dashboard. Explore upcoming events,
            register for activities, and keep track of the events you have joined.
        </p>
    </section>

    <section class="stats">
        <div class="stat-card">
            <h3>My Registered Events</h3>
            <p><?php echo $registeredCount; ?></p>
        </div>
    </section>

    <section class="section">
        <h2>Quick Access</h2>

        <div class="links">
            <a href="events.php">Browse Events</a>
            <a href="my_events.php">My Registered Events</a>
            <a href="profile.php">My Profile</a>
            <a href="assignments.php">Assignments</a>
            <a href="materials.php">Study Materials</a>
            <a href="notices.php">Notices</a>
            <a href="timetable.php">Timetable</a>
        </div>
    </section>

    <section class="section">
        <h2>Upcoming Events</h2>

        <?php if ($upcomingEvents && mysqli_num_rows($upcomingEvents) > 0) { ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Venue</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while ($event = mysqli_fetch_assoc($upcomingEvents)) { ?>
                        <tr>
                            <td><?php echo (int)$event['event_id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($event['event_name'] ?? ''); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($event['event_date'] ?? ''); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($event['venue'] ?? ''); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <a class="browse-button" href="events.php">Register for an Event</a>
        <?php } else { ?>
            <p class="empty">
                There are no upcoming events right now. Please check again later.
            </p>

            <a class="browse-button" href="events.php">Browse All Events</a>
        <?php } ?>
    </section>

</main>

<footer>
    &copy; <?php echo date('Y'); ?> StudentHub Portal
</footer>

</body>
</html>