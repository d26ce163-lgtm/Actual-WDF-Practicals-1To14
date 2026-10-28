
<?php
session_start();
require_once 'db.php';

if (
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'admin'
) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$editStudent = null;

/* Check whether course column exists */
$courseExists = false;
$columnResult = mysqli_query($conn, "SHOW COLUMNS FROM Stu_Register LIKE 'course'");

if ($columnResult && mysqli_num_rows($columnResult) > 0) {
    $courseExists = true;
}

/* ADD STUDENT */
if (isset($_POST['add_student'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $course = trim($_POST['course'] ?? '');

    if (
        $fullname === '' || $email === '' || $mobile === '' ||
        $username === '' || $password === ''
    ) {
        $message = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
    } else {
        $check = $conn->prepare(
            "SELECT username FROM Stu_Register WHERE username = ? OR email = ?"
        );
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $existing = $check->get_result();

        if ($existing->num_rows > 0) {
            $message = "Username or email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";

            if ($courseExists) {
                $stmt = $conn->prepare(
                    "INSERT INTO Stu_Register
                    (fullname, email, mobile, username, password, role, course)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "sssssss",
                    $fullname,
                    $email,
                    $mobile,
                    $username,
                    $hashedPassword,
                    $role,
                    $course
                );
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO Stu_Register
                    (fullname, email, mobile, username, password, role)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "ssssss",
                    $fullname,
                    $email,
                    $mobile,
                    $username,
                    $hashedPassword,
                    $role
                );
            }

            if ($stmt->execute()) {
                $message = "Student added successfully.";
            } else {
                $message = "Error adding student.";
            }
            $stmt->close();
        }
        $check->close();
    }
}

/* LOAD STUDENT FOR EDITING */
if (isset($_GET['edit'])) {
    $usernameToEdit = trim($_GET['edit']);

    $stmt = $conn->prepare(
        "SELECT * FROM Stu_Register
         WHERE username = ? AND LOWER(role) = 'student'"
    );
    $stmt->bind_param("s", $usernameToEdit);
    $stmt->execute();
    $result = $stmt->get_result();
    $editStudent = $result->fetch_assoc();
    $stmt->close();
}

/* UPDATE STUDENT */
if (isset($_POST['update_student'])) {
    $oldUsername = trim($_POST['old_username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $course = trim($_POST['course'] ?? '');

    if ($fullname === '' || $email === '' || $mobile === '') {
        $message = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
    } else {
        $check = $conn->prepare(
            "SELECT username FROM Stu_Register
             WHERE email = ? AND username != ?"
        );
        $check->bind_param("ss", $email, $oldUsername);
        $check->execute();
        $duplicate = $check->get_result();

        if ($duplicate->num_rows > 0) {
            $message = "This email is already used by another account.";
        } else {
            if ($courseExists) {
                $stmt = $conn->prepare(
                    "UPDATE Stu_Register
                     SET fullname = ?, email = ?, mobile = ?, course = ?
                     WHERE username = ? AND LOWER(role) = 'student'"
                );
                $stmt->bind_param(
                    "sssss",
                    $fullname,
                    $email,
                    $mobile,
                    $course,
                    $oldUsername
                );
            } else {
                $stmt = $conn->prepare(
                    "UPDATE Stu_Register
                     SET fullname = ?, email = ?, mobile = ?
                     WHERE username = ? AND LOWER(role) = 'student'"
                );
                $stmt->bind_param(
                    "ssss",
                    $fullname,
                    $email,
                    $mobile,
                    $oldUsername
                );
            }

            if ($stmt->execute()) {
                $message = "Student updated successfully.";
                $stmt->close();
                $check->close();
                header("Location: student_management.php?message=" . urlencode($message));
                exit();
            } else {
                $message = "Error updating student.";
            }
            $stmt->close();
        }
        $check->close();
    }
}

/* DELETE STUDENT */
if (isset($_POST['delete_student'])) {
    $usernameToDelete = trim($_POST['username']);

    $stmt = $conn->prepare(
        "DELETE FROM Stu_Register
         WHERE username = ? AND LOWER(role) = 'student'"
    );
    $stmt->bind_param("s", $usernameToDelete);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = "Student deleted successfully.";
    } else {
        $message = "Student could not be deleted.";
    }

    $stmt->close();
}

/* MESSAGE AFTER REDIRECT */
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

/* SEARCH AND FILTER */
$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');

$where = " WHERE LOWER(role) = 'student' ";
$params = [];
$types = "";

if ($search !== '') {
    $where .= " AND (fullname LIKE ? OR email LIKE ? OR username LIKE ?)";
    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "sss";
}

if ($courseExists && $courseFilter !== '') {
    $where .= " AND course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}

/* PAGINATION */
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

$countSql = "SELECT COUNT(*) AS total FROM Stu_Register" . $where;
$countStmt = $conn->prepare($countSql);

if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$totalStudents = (int)$countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalStudents / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$selectSql = "SELECT fullname, email, mobile, username";
if ($courseExists) {
    $selectSql .= ", course";
}
$selectSql .= " FROM Stu_Register" . $where .
              " ORDER BY username ASC LIMIT ? OFFSET ?";

$listStmt = $conn->prepare($selectSql);

$listParams = $params;
$listTypes = $types . "ii";
$listParams[] = $perPage;
$listParams[] = $offset;

$listStmt->bind_param($listTypes, ...$listParams);
$listStmt->execute();
$students = $listStmt->get_result();

/* COURSE FILTER OPTIONS */
$courses = [];
if ($courseExists) {
    $courseResult = mysqli_query(
        $conn,
        "SELECT DISTINCT course FROM Stu_Register
         WHERE LOWER(role) = 'student'
         AND course IS NOT NULL AND course != ''
         ORDER BY course ASC"
    );

    if ($courseResult) {
        while ($row = mysqli_fetch_assoc($courseResult)) {
            $courses[] = $row['course'];
        }
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | StudentHub</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            margin: 0;
            padding: 25px;
            color: #222;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #00000012;
        }

        h1, h2 {
            color: #263b63;
        }

        .message {
            background: #e8f5e9;
            color: #216b2b;
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 5px 0;
        }

        button, .btn {
            display: inline-block;
            padding: 10px 14px;
            border: none;
            border-radius: 5px;
            background: #263b63;
            color: white;
            text-decoration: none;
            cursor: pointer;
            margin: 3px;
        }

        .edit {
            background: #1677c8;
        }

        .delete {
            background: #c62828;
        }

        .cancel {
            background: #666;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .filter-form input {
            flex: 1;
            min-width: 180px;
        }

        .filter-form select {
            width: auto;
            min-width: 160px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #263b63;
            color: white;
        }

        tr:nth-child(even) {
            background: #f7f8fb;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 3px;
            background: #e4e8f0;
            color: #222;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a.active {
            background: #263b63;
            color: white;
        }

        .top-links {
            margin-bottom: 15px;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="top-links">
        <a class="btn" href="admin_dashboard.php">Dashboard</a>
        <a class="btn" href="show_students.php">Show Students</a>
        <a class="btn" href="logout.php">Logout</a>
    </div>

    <h1>Student Management</h1>
    <p>Logged in as Admin: <?= h($_SESSION['username']) ?></p>

    <?php if ($message !== ''): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <h2><?= $editStudent ? 'Update Student' : 'Add New Student' ?></h2>

    <form method="POST" action="student_management.php">
        <?php if ($editStudent): ?>
            <input type="hidden" name="old_username"
                   value="<?= h($editStudent['username']) ?>">
        <?php endif; ?>

        <div class="form-grid">
            <input type="text" name="fullname" placeholder="Full Name"
                   value="<?= h($editStudent['fullname'] ?? '') ?>" required>

            <input type="email" name="email" placeholder="Email"
                   value="<?= h($editStudent['email'] ?? '') ?>" required>

            <input type="text" name="mobile" placeholder="Mobile Number"
                   value="<?= h($editStudent['mobile'] ?? '') ?>" required>

            <?php if (!$editStudent): ?>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password"
                       placeholder="Password" minlength="8" required>
            <?php else: ?>
                <input type="text" value="<?= h($editStudent['username']) ?>"
                       disabled>
            <?php endif; ?>

            <?php if ($courseExists): ?>
                <input type="text" name="course" placeholder="Course"
                       value="<?= h($editStudent['course'] ?? '') ?>">
            <?php endif; ?>
        </div>

        <?php if ($editStudent): ?>
            <button type="submit" name="update_student">Update Student</button>
            <a class="btn cancel" href="student_management.php">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_student">Add Student</button>
        <?php endif; ?>
    </form>

    <h2>Student List</h2>

    <form method="GET" action="student_management.php" class="filter-form">
        <input type="text" name="search" placeholder="Search name, email, username"
               value="<?= h($search) ?>">

        <?php if ($courseExists): ?>
            <select name="course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= h($course) ?>"
                        <?= $courseFilter === $course ? 'selected' : '' ?>>
                        <?= h($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit">Search / Filter</button>
        <a class="btn cancel" href="student_management.php">Reset</a>
    </form>

    <p>Total students: <strong><?= $totalStudents ?></strong></p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Username</th>
                <?php if ($courseExists): ?>
                    <th>Course</th>
                <?php endif; ?>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if ($students->num_rows > 0): ?>
                <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= h($student['fullname']) ?></td>
                        <td><?= h($student['email']) ?></td>
                        <td><?= h($student['mobile']) ?></td>
                        <td><?= h($student['username']) ?></td>

                        <?php if ($courseExists): ?>
                            <td><?= h($student['course'] ?? '') ?></td>
                        <?php endif; ?>

                        <td>
                            <a class="btn edit"
                               href="student_management.php?edit=<?= urlencode($student['username']) ?>">
                                Edit
                            </a>

                            <form method="POST" action="student_management.php"
                                  style="display:inline"
                                  onsubmit="return confirm('Are you sure you want to delete this student?');">
                                <input type="hidden" name="username"
                                       value="<?= h($student['username']) ?>">
                                <button class="delete" type="submit"
                                        name="delete_student">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= $courseExists ? 6 : 5 ?>">
                        No students found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>"
                   href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>

<?php
$listStmt->close();
?>
