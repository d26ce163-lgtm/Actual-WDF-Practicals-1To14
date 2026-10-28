
<?php
session_start();

$message = $_SESSION['p7_message'] ?? '';
$messageType = $_SESSION['p7_message_type'] ?? '';

unset($_SESSION['p7_message'], $_SESSION['p7_message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 7 - Registration Form</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        h2 {
            text-align: center;
            color: #243b55;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
            border: 1px solid #bbb;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #243b55;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .success {
            color: green;
            background: #e6ffed;
            padding: 10px;
        }

        .error {
            color: red;
            background: #ffe8e8;
            padding: 10px;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>Student Registration Form</h2>

    <?php if ($message !== ''): ?>
        <p class="<?= $messageType === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="p7save.php" method="POST">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name"
               maxlength="100" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               maxlength="254" required>

        <label for="mobile">Mobile Number</label>
        <input type="text" id="mobile" name="mobile"
               pattern="[0-9]{10}" maxlength="10"
               title="Enter exactly 10 digits" required>

        <label for="course">Course</label>
        <select id="course" name="course" required>
            <option value="">Select Course</option>
            <option value="BTech CE">BTech CE</option>
            <option value="BTech IT">BTech IT</option>
            <option value="BCA">BCA</option>
            <option value="MCA">MCA</option>
            <option value="Other">Other</option>
        </select>

        <label for="message">Message (Optional)</label>
        <textarea id="message" name="message"
                  rows="4" maxlength="1000"></textarea>

        <button type="submit">Submit Registration</button>
    </form>
</div>
</body>
</html>
