
    <?php
    session_start();

    if (
        !isset($_SESSION['username']) ||
        !isset($_SESSION['role']) ||
        strtolower($_SESSION['role']) !== 'admin'
    ) {
        header('Location: admin_login.php');
        exit();
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AJAX Event CRUD - StudentHub</title>

    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f3f5f9;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 1100px;
        margin: auto;
        background: white;
        padding: 22px;
        border-radius: 10px;
    }
    h1, h2 { color: #263b63; }
    form { margin-bottom: 20px; }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #bbb;
        border-radius: 5px;
        box-sizing: border-box;
    }
    .full { grid-column: 1 / -1; }
    button, .link {
        display: inline-block;
        background: #263b63;
        color: white;
        padding: 9px 13px;
        border: 0;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        margin: 3px;
    }
    .edit { background: #1677c8; }
    .delete { background: #c62828; }
    .cancel { background: #666; }
    #message {
        padding: 12px;
        border-radius: 5px;
        margin: 12px 0;
        display: none;
    }
    #message.success { display: block; background: #e4f6e8; color: #216b2b; }
    #message.error { display: block; background: #fde8e8; color: #9b1c1c; }
    #loading { color: #555; margin: 12px 0; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #263b63; color: white; }
    tr:nth-child(even) { background: #f7f8fb; }
    @media(max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
        .full { grid-column: auto; }
    }
    </style>
    </head>

    <body>
    <div class="container">
        <p>
            <a class="link" href="admin_dashboard.php">Dashboard</a>
            <a class="link" href="manage_events.php">Manage Events</a>
            <a class="link" href="logout.php">Logout</a>
        </p>

        <h1>Event Management — Fetch API</h1>
        <p>Admin: <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></p>

        <div id="message" role="status" aria-live="polite"></div>
        <div id="loading" role="status" aria-live="polite"></div>

        <h2 id="formTitle">Add Event</h2>

        <form id="eventForm">
            <input type="hidden" id="event_id" name="event_id">

            <div class="form-grid">
                <div>
                    <label for="event_name">Event Title</label>
                    <input id="event_name" name="event_name" maxlength="150" required>
                </div>

                <div>
                    <label for="organization">Organization</label>
                    <input id="organization" name="organization" maxlength="150" required>
                </div>

                <div>
                    <label for="event_date">Event Date</label>
                    <input id="event_date" name="event_date" type="date" required>
                </div>

                <div>
                    <label for="fees">Fees</label>
                    <input id="fees" name="fees" type="number" min="0" step="0.01" required>
                </div>

                <div class="full">
                    <label for="venue">Venue</label>
                    <input id="venue" name="venue" maxlength="200" required>
                </div>

                <div class="full">
                    <button type="submit" id="saveButton">Add Event</button>
                    <button type="button" class="cancel" id="cancelButton" hidden>
                        Cancel Edit
                    </button>
                </div>
            </div>
        </form>

        <h2>Events List</h2>

        <label for="search">Search events</label>
        <input id="search" type="search" placeholder="Search title, organization, or venue">

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Organization</th>
                    <th>Date</th>
                    <th>Fees</th>
                    <th>Venue</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="eventsBody">
                    <tr><td colspan="7">Loading events...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const apiUrl = 'events_api.php';

    const form = document.getElementById('eventForm');
    const messageBox = document.getElementById('message');
    const loadingBox = document.getElementById('loading');
    const eventsBody = document.getElementById('eventsBody');
    const searchInput = document.getElementById('search');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');

    let debounceTimer;

    function showMessage(message, type = 'success') {
        messageBox.textContent = message;
        messageBox.className = type;
    }

    function setLoading(isLoading, text = 'Loading...') {
        loadingBox.textContent = isLoading ? text : '';
        saveButton.disabled = isLoading;
    }

    async function requestApi(url, options = {}) {
        const response = await fetch(url, options);
        const text = await response.text();

        let result;

        try {
            result = JSON.parse(text);
        } catch {
            throw new Error('The server returned invalid JSON. Check PHP errors.');
        }

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Request failed.');
        }

        return result;
    }

    function makeCell(value) {
        const td = document.createElement('td');
        td.textContent = value ?? '';
        return td;
    }

    async function loadEvents(search = '') {
        setLoading(true, 'Loading events...');

        try {
            const url = apiUrl + '?search=' + encodeURIComponent(search);
            const result = await requestApi(url);
            eventsBody.replaceChildren();

            if (!result.data || result.data.length === 0) {
                const row = document.createElement('tr');
                const cell = makeCell('No events found.');
                cell.colSpan = 7;
                row.appendChild(cell);
                eventsBody.appendChild(row);
                return;
            }

            result.data.forEach(event => {
                const row = document.createElement('tr');

                row.appendChild(makeCell(event.event_id));
                row.appendChild(makeCell(event.event_name));
                row.appendChild(makeCell(event.organization));
                row.appendChild(makeCell(event.event_date));
                row.appendChild(makeCell(Number(event.fees).toFixed(2)));
                row.appendChild(makeCell(event.venue));

                const actionCell = document.createElement('td');

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'edit';
                editButton.textContent = 'Edit';
                editButton.addEventListener('click', () => editEvent(event));

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'delete';
                deleteButton.textContent = 'Delete';
                deleteButton.addEventListener('click', () => deleteEvent(event.event_id));

                actionCell.append(editButton, deleteButton);
                row.appendChild(actionCell);
                eventsBody.appendChild(row);
            });
        } catch (error) {
            showMessage(error.message, 'error');
            eventsBody.replaceChildren();

            const row = document.createElement('tr');
            const cell = makeCell('Could not load events.');
            cell.colSpan = 7;
            row.appendChild(cell);
            eventsBody.appendChild(row);
        } finally {
            setLoading(false);
        }
    }

    function editEvent(event) {
        document.getElementById('event_id').value = event.event_id;
        document.getElementById('event_name').value = event.event_name;
        document.getElementById('organization').value = event.organization;
        document.getElementById('event_date').value = event.event_date;
        document.getElementById('fees').value = event.fees;
        document.getElementById('venue').value = event.venue;

        document.getElementById('formTitle').textContent = 'Update Event';
        saveButton.textContent = 'Update Event';
        cancelButton.hidden = false;

        document.getElementById('event_name').focus();
    }

    function resetForm() {
        form.reset();
        document.getElementById('event_id').value = '';
        document.getElementById('formTitle').textContent = 'Add Event';
        saveButton.textContent = 'Add Event';
        cancelButton.hidden = true;
    }

    cancelButton.addEventListener('click', resetForm);

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const eventId = document.getElementById('event_id').value;

        const payload = {
            action: eventId ? 'update' : 'add',
            event_id: eventId ? Number(eventId) : undefined,
            event_name: document.getElementById('event_name').value.trim(),
            organization: document.getElementById('organization').value.trim(),
            event_date: document.getElementById('event_date').value,
            fees: document.getElementById('fees').value,
            venue: document.getElementById('venue').value.trim()
        };

        setLoading(true, 'Saving event...');

        try {
            const result = await requestApi(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            showMessage(result.message, 'success');
            resetForm();
            await loadEvents(searchInput.value.trim());
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            setLoading(false);
        }
    });

    async function deleteEvent(eventId) {
        if (!confirm('Are you sure you want to delete this event?')) {
            return;
        }

        setLoading(true, 'Deleting event...');

        try {
            const result = await requestApi(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete',
                    event_id: Number(eventId)
                })
            });

            showMessage(result.message, 'success');

            if (document.getElementById('event_id').value === String(eventId)) {
                resetForm();
            }

            await loadEvents(searchInput.value.trim());
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            setLoading(false);
        }
    }

    /* Debounced search: wait 300 ms after typing */
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            loadEvents(searchInput.value.trim());
        }, 300);
    });

    /* Initial load */
    loadEvents();
    </script>
    </body>
    </html>
