
<?php
session_start();

function redirectWithMessage($message, $type)
{
    $_SESSION['p7_message'] = $message;
    $_SESSION['p7_message_type'] = $type;

    header("Location: p7index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed. Please submit the registration form.");
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$course = trim($_POST['course'] ?? '');
$message = trim($_POST['message'] ?? '');

$name = preg_replace('/\s+/', ' ', $name);

$allowedCourses = [
    'BTech CE',
    'BTech IT',
    'BCA',
    'MCA',
    'Other'
];

if ($name === '' || $email === '' || $mobile === '' || $course === '') {
    redirectWithMessage("Please fill in all required fields.", "error");
}

if (strlen($name) > 100) {
    redirectWithMessage("Name must not exceed 100 characters.", "error");
}

if (!preg_match("/^[\p{L}\p{M} .'-]+$/u", $name)) {
    redirectWithMessage("Please enter a valid name.", "error");
}

if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage("Please enter a valid email address.", "error");
}

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    redirectWithMessage("Mobile number must contain exactly 10 digits.", "error");
}

if (!in_array($course, $allowedCourses, true)) {
    redirectWithMessage("Please select a valid course.", "error");
}

if (strlen($message) > 1000) {
    redirectWithMessage("Message must not exceed 1000 characters.", "error");
}

/* Store CSV in the same folder as this PHP file. */
$csvFile = __DIR__ . '/p7registration.csv';

$file = fopen($csvFile, 'c+');

if ($file === false) {
    redirectWithMessage("Unable to open the CSV file. Check folder permissions.", "error");
}

if (!flock($file, LOCK_EX)) {
    fclose($file);
    redirectWithMessage("Unable to lock the CSV file. Please try again.", "error");
}

fseek($file, 0, SEEK_END);
$fileSize = ftell($file);

if ($fileSize === 0) {
    fputcsv($file, [
        'Registration Date',
        'Full Name',
        'Email',
        'Mobile',
        'Course',
        'Message'
    ]);
}

fputcsv($file, [
    date('Y-m-d H:i:s'),
    $name,
    $email,
    $mobile,
    $course,
    $message
]);

fflush($file);
flock($file, LOCK_UN);
fclose($file);

redirectWithMessage("Registration saved successfully!", "success");
?>
