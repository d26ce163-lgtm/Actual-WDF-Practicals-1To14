
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
    <title>Timetable - StudentHub</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            color: #1e293b;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
        }

        h1 {
            color: #0f766e;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border: 1px solid #cbd5e1;
        }

        th {
            background: #0f766e;
            color: white;
        }

        tr:nth-child(even) {
            background: #f0fdfa;
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
    <h1>Class Timetable</h1>

    <p>Student: <?= htmlspecialchars($_SESSION['username']) ?></p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>09:00 - 10:00</th>
                    <th>10:00 - 11:00</th>
                    <th>11:15 - 12:15</th>
                    <th>12:15 - 01:15</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Monday</td>
                    <td>DSA</td>
                    <td>DBMS</td>
                    <td>Computer Networks</td>
                    <td>Practical</td>
                </tr>

                <tr>
                    <td>Tuesday</td>
                    <td>DBMS</td>
                    <td>Java</td>
                    <td>DSA</td>
                    <td>Practical</td>
                </tr>

                <tr>
                    <td>Wednesday</td>
                    <td>Java</td>
                    <td>Computer Networks</td>
                    <td>DBMS</td>
                    <td>Library</td>
                </tr>

                <tr>
                    <td>Thursday</td>
                    <td>DSA</td>
                    <td>Java</td>
                    <td>Practical</td>
                    <td>Computer Networks</td>
                </tr>

                <tr>
                    <td>Friday</td>
                    <td>Computer Networks</td>
                    <td>DBMS</td>
                    <td>Java</td>
                    <td>Practical</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p>Note: Replace these sample subjects and timings with your actual timetable.</p>

    <a class="back" href="student_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
