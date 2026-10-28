<?php
session_start();
require_once 'db.php';

if (
    !isset($_SESSION['username']) ||
    strtolower($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: login.html');
    exit;
}

function getCount($conn, $sql) {
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    return 0;
}

$adminName = htmlspecialchars($_SESSION['username']);

$totalStudents = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM Stu_Register
     WHERE LOWER(role) = 'student'"
);

$totalEvents = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM Events"
);

$totalRegistrations = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM Event_Registrations"
);

$recentEvents = mysqli_query(
    $conn,
    "SELECT event_id, event_name, event_date, venue
     FROM Events
     ORDER BY event_date DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub</title>

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
            background: #172554;
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
            color: #cbd5e1;
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
            margin-bottom: 25px;
        }

        .welcome h2 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000d;
            border-left: 5px solid #2563eb;
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .stat-card p {
            font-size: 32px;
            font-weight: bold;
            color: #172554;
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
            background: #eff6ff;
            color: #1d4ed8;
            text-decoration: none;
            padding: 18px;
            border-radius: 9px;
            font-weight: bold;
            border: 1px solid #dbeafe;
            transition: 0.2s;
        }

        .links a:hover {
            background: #dbeafe;
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
            background: #eff6ff;
            color: #1e3a8a;
        }

        .empty {
            color: #64748b;
            padding: 15px 0;
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
        <p>Administrator Dashboard</p>
    </div>

    <a class="logout" href="logout.php">Logout</a>
</header>

<main class="container">

    <section class="welcome">
        <h2>Welcome, <?php echo $adminName; ?>!</h2>
        <p>Manage students, events, and event participation from one place.</p>
    </section>

    <section class="stats">
        <div class="stat-card">
            <h3>Total Students</h3>
            <p><?php echo $totalStudents; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Events</h3>
            <p><?php echo $totalEvents; ?></p>
        </div>

        <div class="stat-card">
            <h3>Event Registrations</h3>
            <p><?php echo $totalRegistrations; ?></p>
        </div>
    </section>

    <section class="section">
        <h2>Management Options</h2>

        <div class="links">
            <a href="show_students.php">Manage / View Students</a>
            <a href="manage_events.php">Manage Events</a>
            <a href="event_participants.php">View Event Participants</a>
            <a href="events.php">Browse Events</a>
            <a href="student_management.php">
             Practical 11: Student CRUD
            </a>

            <a href="manage_events.php">
                Practical 12: Event CRUD
            </a>
        </div>
    </section>

    <section class="section">
        <h2>Recent Events</h2>

        <?php if ($recentEvents && mysqli_num_rows($recentEvents) > 0) { ?>
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
                    <?php while ($event = mysqli_fetch_assoc($recentEvents)) { ?>
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
        <?php } else { ?>
            <p class="empty">No events found. Add an event to get started.</p>
        <?php } ?>
    </section>

</main>

<footer>
    &copy; <?php echo date('Y'); ?> StudentHub Portal
</footer>

</body>
</html>