
<?php
require_once 'db.php';
require_once 'auth.php';
requireAdmin();

$message = '';

$uploadDir = __DIR__ . '/uploads/event_posters/';
$uploadUrl = 'uploads/event_posters/';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function uploadEventPoster($file, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => null, 'error' => ''];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null,
                'error' => 'Poster upload failed. Please try again.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'filename' => null,
                'error' => 'Invalid uploaded file.'];
    }

    if ($file['size'] <= 0 || $file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'filename' => null,
                'error' => 'Poster must be greater than 0 bytes and at most 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mime])) {
        return ['success' => false, 'filename' => null,
                'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.'];
    }

    if (@getimagesize($file['tmp_name']) === false) {
        return ['success' => false, 'filename' => null,
                'error' => 'The uploaded file is not a valid image.'];
    }

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'filename' => null,
                    'error' => 'Could not create the poster upload folder.'];
        }
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return ['success' => false, 'filename' => null,
                'error' => 'Could not save the poster. Check folder permissions.'];
    }

    return ['success' => true, 'filename' => $filename, 'error' => ''];
}

function deletePosterFile($filename, $uploadDir) {
    if (!$filename) {
        return;
    }

    // Only delete simple generated image filenames.
    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $filename)) {
        return;
    }

    $path = $uploadDir . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}

/* ADD EVENT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_event'])) {

    $eventName = trim($_POST['event_name'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $fees = trim($_POST['fees'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'Open';

    if ($eventName === '' || $organization === '' ||
        $eventDate === '' || $fees === '' || $venue === '') {
        $message = 'Please fill in all required fields.';
    } elseif (!is_numeric($fees) || (float)$fees < 0) {
        $message = 'Fees must be a non-negative number.';
    } elseif (!in_array($status, ['Open', 'Closed'], true)) {
        $message = 'Invalid event status.';
    } elseif (
        !DateTime::createFromFormat('!Y-m-d', $eventDate) ||
        DateTime::createFromFormat('!Y-m-d', $eventDate)->format('Y-m-d') !== $eventDate
    ) {
        $message = 'Please enter a valid event date.';
    } else {
        $upload = uploadEventPoster($_FILES['poster'] ?? null, $uploadDir);

        if (!$upload['success']) {
            $message = $upload['error'];
        } else {
            $poster = $upload['filename'];
            $feeValue = (float)$fees;

            $stmt = mysqli_prepare(
                $conn,
                'INSERT INTO Events
                (event_name, organization, event_date, fees, venue, category, status, poster)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt, 'sssdssss',
                    $eventName, $organization, $eventDate, $feeValue,
                    $venue, $category, $status, $poster
                );

                if (mysqli_stmt_execute($stmt)) {
                    logAction(
                        $conn,
                        $_SESSION['username'],
                        'ADD_EVENT',
                        'Added event: ' . $eventName
                    );
                    $message = 'Event added successfully.';
                } else {
                    if ($poster) {
                        deletePosterFile($poster, $uploadDir);
                    }
                    $message = 'Could not add event. Check that the category, status, and poster columns exist.';
                }

                mysqli_stmt_close($stmt);
            } else {
                if ($poster) {
                    deletePosterFile($poster, $uploadDir);
                }
                $message = 'Database query error. Check the Events table columns.';
            }
        }
    }
}

/* UPDATE EVENT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_event'])) {

    $eventId = (int)($_POST['event_id'] ?? 0);
    $eventName = trim($_POST['event_name'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $fees = trim($_POST['fees'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'Open';

    if ($eventId <= 0 || $eventName === '' || $organization === '' ||
        $eventDate === '' || $fees === '' || $venue === '') {
        $message = 'Please fill in all required fields.';
    } elseif (!is_numeric($fees) || (float)$fees < 0) {
        $message = 'Fees must be a non-negative number.';
    } elseif (!in_array($status, ['Open', 'Closed'], true)) {
        $message = 'Invalid event status.';
    } else {
        $oldStmt = mysqli_prepare(
            $conn,
            'SELECT poster FROM Events WHERE event_id = ?'
        );
        mysqli_stmt_bind_param($oldStmt, 'i', $eventId);
        mysqli_stmt_execute($oldStmt);
        $oldResult = mysqli_stmt_get_result($oldStmt);
        $oldEvent = mysqli_fetch_assoc($oldResult);
        mysqli_stmt_close($oldStmt);

        if (!$oldEvent) {
            $message = 'Event not found.';
        } else {
            $upload = uploadEventPoster($_FILES['poster'] ?? null, $uploadDir);

            if (!$upload['success']) {
                $message = $upload['error'];
            } else {
                $oldPoster = $oldEvent['poster'] ?? null;
                $newPoster = $upload['filename'] ?? $oldPoster;
                $feeValue = (float)$fees;

                $stmt = mysqli_prepare(
                    $conn,
                    'UPDATE Events
                     SET event_name = ?, organization = ?, event_date = ?,
                         fees = ?, venue = ?, category = ?, status = ?, poster = ?
                     WHERE event_id = ?'
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt, 'sssdssssi',
                        $eventName, $organization, $eventDate, $feeValue,
                        $venue, $category, $status, $newPoster, $eventId
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        if ($upload['filename'] !== null && $oldPoster) {
                            deletePosterFile($oldPoster, $uploadDir);
                        }

                        logAction(
                            $conn,
                            $_SESSION['username'],
                            'UPDATE_EVENT',
                            'Updated event ID: ' . $eventId
                        );
                        $message = 'Event updated successfully.';
                    } else {
                        if ($upload['filename']) {
                            deletePosterFile($upload['filename'], $uploadDir);
                        }
                        $message = 'Could not update event.';
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    if ($upload['filename']) {
                        deletePosterFile($upload['filename'], $uploadDir);
                    }
                    $message = 'Database query error while updating event.';
                }
            }
        }
    }
}

/* DELETE EVENT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_event'])) {

    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($eventId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            'SELECT event_name, poster FROM Events WHERE event_id = ?'
        );
        mysqli_stmt_bind_param($stmt, 'i', $eventId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $event = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($event) {
            $deleteStmt = mysqli_prepare(
                $conn,
                'DELETE FROM Events WHERE event_id = ?'
            );
            mysqli_stmt_bind_param($deleteStmt, 'i', $eventId);

            if (mysqli_stmt_execute($deleteStmt) &&
                mysqli_stmt_affected_rows($deleteStmt) > 0) {
                deletePosterFile($event['poster'] ?? null, $uploadDir);

                logAction(
                    $conn,
                    $_SESSION['username'],
                    'DELETE_EVENT',
                    'Deleted event: ' . $event['event_name']
                );
                $message = 'Event deleted successfully.';
            } else {
                $message = 'Could not delete event.';
            }

            mysqli_stmt_close($deleteStmt);
        } else {
            $message = 'Event not found.';
        }
    } else {
        $message = 'Invalid event ID.';
    }
}

/* LOAD EVENT FOR EDITING */
$editEvent = null;

if (isset($_GET['edit'])) {
    $eventId = (int)$_GET['edit'];

    if ($eventId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            'SELECT * FROM Events WHERE event_id = ?'
        );
        mysqli_stmt_bind_param($stmt, 'i', $eventId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $editEvent = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

/* LOAD EVENT LIST */
$events = mysqli_query(
    $conn,
    'SELECT event_id, event_name, organization, event_date,
            fees, venue, category, status, poster
     FROM Events ORDER BY event_id DESC'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Events - StudentHub</title>
<link rel="stylesheet" href="admin.css">

<style>
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.form-grid label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}
.form-grid input, .form-grid select {
    width: 100%;
    padding: 11px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.form-grid .full { grid-column: 1 / -1; }
.message {
    padding: 12px;
    background: #e0f2fe;
    border-radius: 8px;
    margin: 14px 0;
}
.btn { border: 0; cursor: pointer; }
.poster {
    width: 90px;
    max-height: 100px;
    object-fit: cover;
    border-radius: 6px;
}
.actions { display: flex; gap: 6px; flex-wrap: wrap; }
.actions form { display: inline; }
@media(max-width:600px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-grid .full { grid-column: auto; }
}
</style>
</head>

<body>
<div class="layout">
    <aside class="sidebar">
        <h2>StudentHub</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a class="active" href="manage_events.php">Add / Manage Events</a>
        <a href="events.php">View Events</a>
        <a href="show_students.php">Manage Students</a>
        <a href="admin_logs.php">Audit Logs</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Manage Events</h1>
                <p>Add, edit, or delete events and upload their posters.</p>
            </div>
            <span class="role-pill">
                Admin: <?= h($_SESSION['username']) ?>
            </span>
        </header>

        <?php if ($message !== ''): ?>
            <div class="message"><?= h($message) ?></div>
        <?php endif; ?>

        <section class="panel">
            <h2><?= $editEvent ? 'Update Event' : 'Add New Event' ?></h2>

            <form method="post" action="manage_events.php"
                  class="form-grid" enctype="multipart/form-data">

                <?php if ($editEvent): ?>
                    <input type="hidden" name="event_id"
                           value="<?= (int)$editEvent['event_id'] ?>">
                <?php endif; ?>

                <div>
                    <label for="event_name">Event Title</label>
                    <input id="event_name" name="event_name" maxlength="150"
                           value="<?= h($editEvent['event_name'] ?? '') ?>"
                           required>
                </div>

                <div>
                    <label for="organization">Event Organization</label>
                    <input id="organization" name="organization" maxlength="150"
                           value="<?= h($editEvent['organization'] ?? '') ?>"
                           required>
                </div>

                <div>
                    <label for="event_date">Event Date</label>
                    <input id="event_date" type="date" name="event_date"
                           value="<?= h($editEvent['event_date'] ?? '') ?>"
                           required>
                </div>

                <div>
                    <label for="fees">Event Fees</label>
                    <input id="fees" type="number" name="fees"
                           min="0" step="0.01"
                           value="<?= h($editEvent['fees'] ?? '') ?>"
                           required>
                </div>

                <div>
                    <label for="category">Category</label>
                    <input id="category" name="category" maxlength="100"
                           value="<?= h($editEvent['category'] ?? '') ?>"
                           placeholder="Workshop, Seminar, Sports">
                </div>

                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Open"
                            <?= ($editEvent['status'] ?? 'Open') === 'Open' ? 'selected' : '' ?>>
                            Open
                        </option>
                        <option value="Closed"
                            <?= ($editEvent['status'] ?? '') === 'Closed' ? 'selected' : '' ?>>
                            Closed
                        </option>
                    </select>
                </div>

                <div class="full">
                    <label for="venue">Venue</label>
                    <input id="venue" name="venue" maxlength="200"
                           value="<?= h($editEvent['venue'] ?? '') ?>"
                           required>
                </div>

                <div class="full">
                    <label for="poster">
                        Event Poster (JPG, PNG, WEBP — maximum 2 MB)
                    </label>
                    <input id="poster" type="file" name="poster"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                    <?php if ($editEvent && !empty($editEvent['poster'])): ?>
                        <p>Current poster:</p>
                        <img class="poster"
                             src="<?= h($uploadUrl . basename($editEvent['poster'])) ?>"
                             alt="Current event poster">
                        <p>Leave the file field empty to keep the current poster.</p>
                    <?php endif; ?>
                </div>

                <div class="full">
                    <?php if ($editEvent): ?>
                        <button class="btn" type="submit" name="update_event">
                            Update Event
                        </button>
                        <a class="btn" href="manage_events.php">Cancel</a>
                    <?php else: ?>
                        <button class="btn" type="submit" name="add_event">
                            Add Event
                        </button>
                    <?php endif; ?>
                    <a href="events.php">View Events</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <h2>Existing Events</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Organization</th>
                        <th>Date</th>
                        <th>Fees</th>
                        <th>Venue</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if ($events && mysqli_num_rows($events) > 0): ?>
                        <?php while ($e = mysqli_fetch_assoc($events)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($e['poster'])): ?>
                                        <img class="poster"
                                             src="<?= h($uploadUrl . basename($e['poster'])) ?>"
                                             alt="Event poster">
                                    <?php else: ?>
                                        No poster
                                    <?php endif; ?>
                                </td>
                                <td><?= h($e['event_name']) ?></td>
                                <td><?= h($e['organization']) ?></td>
                                <td><?= h($e['event_date']) ?></td>
                                <td><?= h($e['fees']) ?></td>
                                <td><?= h($e['venue']) ?></td>
                                <td><?= h($e['category'] ?? '') ?></td>
                                <td><?= h($e['status'] ?? 'Open') ?></td>
                                <td>
                                    <div class="actions">
                                        <a class="btn"
                                           href="manage_events.php?edit=<?= (int)$e['event_id'] ?>">
                                            Edit
                                        </a>

                                        <form method="post" action="manage_events.php"
                                              onsubmit="return confirm('Delete this event?');">
                                            <input type="hidden" name="event_id"
                                                   value="<?= (int)$e['event_id'] ?>">
                                            <button class="btn" type="submit"
                                                    name="delete_event">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">No events added yet.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>

<?php
if ($events) {
    mysqli_free_result($events);
}
mysqli_close($conn);
?>
