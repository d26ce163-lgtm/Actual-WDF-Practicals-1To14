STUDENTHUB - UPDATED ROLE AND EVENT MANAGEMENT

COPY these files into your existing P1ToP4 folder, overwriting only files with matching names:
register.html, register.php, login.html, login.php, auth.php, admin_dashboard.php, student_dashboard.php, logout.php, events.css
Add the new files: events.php, manage_events.php, SQL_SETUP_PRACTICAL14.sql.
Keep your existing db.php and existing project assets. The database name should be Studenthub.

DATABASE:
1. Open phpMyAdmin > Studenthub > SQL.
2. Run the SQL file statements one by one. If role or created_at already exists, skip the matching ALTER TABLE statement.
3. If Events already exists with only event_name/event_date/venue, run the optional ALTER statements at the bottom only if those columns are missing.
4. Ensure Stu_Login has username and password columns as the existing register.php expects.

ROLE SELECTION:
The registration form asks Student or Admin. To prevent anyone from freely making an admin account, admin selection requires a code. Default demo code: SH-ADMIN-2026. Change ADMIN_REGISTRATION_CODE in register.php before sharing your project.
If your project is only a classroom demo, do not publish the admin code. For a real public site, admin accounts should be created only by an existing administrator.

EVENTS:
Admin logs in as Admin, then chooses Add / Manage Events > Add Event. The page is protected server-side with requireAdmin().
Students can log in as Student and open events.php to view events; they cannot open manage_events.php.

OPEN THROUGH XAMPP:
http://localhost/P1ToP4/register.html
Do not double-click PHP files or open them directly from the filesystem.

If your database uses different column names, adapt the SQL and PHP queries to match the real table structure.
