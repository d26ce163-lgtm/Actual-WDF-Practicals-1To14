
<?php
session_start();
require_once 'db.php';

if (
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    strtolower((string)$_SESSION['role']) !== 'admin'
) {
    header('Location: admin_login.php');
    exit;
}

$message = '';
$type = 'success';

if (isset($_SESSION['student_message'])) {
    $message = $_SESSION['student_message'];
    $type = $_SESSION['student_message_type'] ?? 'success';
    unset($_SESSION['student_message'], $_SESSION['student_message_type']);
}

$hasCourse = false;
$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM Stu_Register LIKE 'course'");

if ($columnCheck && mysqli_num_rows($columnCheck) > 0) {
    $hasCourse = true;
}

$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;

$where = " WHERE LOWER(role) = 'student' ";
$params = [];
$types = '';

if ($search !== '') {
    if ($hasCourse) {
        $where .= " AND (fullname LIKE ? OR email LIKE ? OR username LIKE ? OR course LIKE ?)";
        $term = '%' . $search . '%';
        $params = [$term, $term, $term, $term];
        $types = 'ssss';
    } else {
        $where .= " AND (fullname LIKE ? OR email LIKE ? OR username LIKE ?)";
        $term = '%' . $search . '%';
        $params = [$term, $term, $term];
        $types = 'sss';
    }
}

if ($hasCourse && $courseFilter !== '') {
    $where .= " AND course = ?";
    $params[] = $courseFilter;
    $types .= 's';
}

$countSql = "SELECT COUNT(*) AS total FROM Stu_Register" . $where;
$stmt = mysqli_prepare($conn, $countSql);

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$countResult = mysqli_stmt_get_result($stmt);
$totalStudents = (int)mysqli_fetch_assoc($countResult)['total'];
mysqli_stmt_close($stmt);

$totalPages = max(1, (int)ceil($totalStudents / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;

$columns = "fullname, email, mobile, username";
if ($hasCourse) {
    $columns .= ", course";
}

$sql = "SELECT $columns FROM Stu_Register" . $where .
       " ORDER BY username ASC LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
$listTypes = $types . 'ii';
$listParams = array_merge($params, [$limit, $offset]);

mysqli_stmt_bind_param($stmt, $listTypes, ...$listParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$courses = [];
if ($hasCourse) {
    $courseResult = mysqli_query(
        $conn,
        "SELECT DISTINCT course FROM Stu_Register
         WHERE LOWER(role) = 'student'
         ORDER BY course"
    );

    if ($courseResult) {
        while ($row = mysqli_fetch_assoc($courseResult)) {
            if ($row['course'] !== '') {
                $courses[] = $row['course'];
            }
        }
        mysqli_free_result($courseResult);
    }
}

function pageUrl($page, $search, $courseFilter) {
    return '?' . http_build_query([
        'page' => $page,
        'search' => $search,
        'course' => $courseFilter
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Management - StudentHub</title>

<style>
*{box-sizing:border-box}
body{margin:0;padding:25px;font-family:Arial,sans-serif;background:#f3f5fa;color:#1e293b}
.container{max-width:1200px;margin:auto;padding:25px;background:white;border-radius:12px;box-shadow:0 4px 15px #00000012}
.top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
h1{color:#1e3a8a}
h2{margin-top:28px;color:#1e3a8a}
a.button,button{display:inline-block;padding:10px 14px;margin:4px 3px 4px 0;border:0;border-radius:6px;background:#1d4ed8;color:white;text-decoration:none;cursor:pointer;font-size:14px}
.delete{background:#dc2626}
.edit{background:#0f766e}
input,select{padding:11px;margin:5px 5px 5px 0;border:1px solid #cbd5e1;border-radius:6px;max-width:100%}
.message{padding:12px;margin:15px 0;border-radius:6px;background:#dcfce7;color:#166534}
.error{background:#fee2e2;color:#991b1b}
.table-wrap{overflow-x:auto;margin-top:20px}
table{width:100%;border-collapse:collapse;min-width:700px}
th,td{padding:12px;text-align:left;border-bottom:1px solid #e2e8f0}
th{background:#1e3a8a;color:white}
tr:hover{background:#eff6ff}
.empty{text-align:center;color:#64748b;padding:20px}
.pagination{margin-top:20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.pagination a{padding:9px 12px;border-radius:5px;background:#dbeafe;color:#1e3a8a;text-decoration:none}
.pagination .current{background:#1e3a8a;color:white}
.actions{white-space:nowrap}
.inline-form{display:inline}
@media(max-width:600px){body{padding:10px}.container{padding:15px}.top{align-items:flex-start}}
</style>
</head>

<body>
<div class="container">

<div class="top">
    <h1>Student Management</h1>
    <div>
        <a class="button" href="admin_dashboard.php">Dashboard</a>
        <a class="button" href="student_management.php">Full CRUD Page</a>
        <a class="button" href="logout.php">Logout</a>
    </div>
</div>

<?php if ($message !== ''): ?>
<div class="message <?= $type === 'error' ? 'error' : '' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<h2>Registered Students</h2>

<p>Total Matching Students:
<strong><?= $totalStudents ?></strong></p>

<form method="get" action="show_students.php">
    <input type="text" name="search"
           placeholder="Search name, email or username<?= $hasCourse ? ' or course' : '' ?>"
           value="<?= htmlspecialchars($search) ?>">

    <?php if ($hasCourse): ?>
    <select name="course">
        <option value="">All Courses</option>
        <?php foreach ($courses as $course): ?>
        <option value="<?= htmlspecialchars($course) ?>"
            <?= $courseFilter === $course ? 'selected' : '' ?>>
            <?= htmlspecialchars($course) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <button type="submit">Search / Filter</button>
    <a class="button" href="show_students.php">Reset</a>
</form>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>Full Name</th>
    <th>Email</th>
    <th>Mobile</th>
    <th>Username</th>
    <?php if ($hasCourse): ?><th>Course</th><?php endif; ?>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($result) > 0): ?>
    <?php while ($student = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= htmlspecialchars($student['fullname'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['email'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['mobile'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['username'] ?? '') ?></td>

        <?php if ($hasCourse): ?>
        <td><?= htmlspecialchars($student['course'] ?? '') ?></td>
        <?php endif; ?>

        <td class="actions">
            <a class="button edit"
               href="UpdateStu.php?username=<?= urlencode($student['username']) ?>">
               Edit
            </a>

            <form class="inline-form" method="post" action="DelStu.php"
                  onsubmit="return confirm('Are you sure you want to delete this student?');">
                <input type="hidden" name="username"
                       value="<?= htmlspecialchars($student['username']) ?>">
                <button class="delete" type="submit" name="confirm" value="yes">
                    Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="<?= $hasCourse ? 6 : 5 ?>" class="empty">
            No student accounts found.
        </td>
    </tr>
<?php endif; ?>

</tbody>
</table>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="<?= htmlspecialchars(pageUrl($page - 1, $search, $courseFilter)) ?>">Previous</a>
    <?php endif; ?>

    <span>Page <?= $page ?> of <?= $totalPages ?></span>

    <?php if ($page < $totalPages): ?>
        <a href="<?= htmlspecialchars(pageUrl($page + 1, $search, $courseFilter)) ?>">Next</a>
    <?php endif; ?>
</div>

</div>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($conn);
?>
