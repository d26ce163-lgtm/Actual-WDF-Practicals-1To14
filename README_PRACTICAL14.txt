STUDENTHUB PORTAL - PRACTICAL 14 FILES

IMPORTANT
These files are designed for the existing project in your uploaded ZIP.
Keep your existing db.php, login.html, register.html, CSS, images, and other portal pages.
The database name in db.php must remain Studenthub (capitalization as currently configured).

FILES TO CREATE
1. auth.php
2. student_dashboard.php
3. admin_dashboard.php
4. admin_logs.php
5. logout.php
6. admin.css
7. show_students.php (replace existing)
8. UpdateStu.php (replace existing)
9. DelStu.php (replace existing)

FILES TO REPLACE WITH THE VERSIONS IN THIS PACKAGE
1. login.php
2. register.php


DATABASE REQUIREMENTS (DO THIS AFTER CREATING/COPYING FILES)
- Stu_Register must have fullname, email, mobile, username, password, role, and created_at columns.
- Events table: event_id, event_name, event_date, venue.
- Event_Registrations table: registration_id, username, event_id, registered_at.
- audit_logs table: log_id, username, action, details, created_at.

The SQL to create/alter these tables will be provided separately, after the files are in place.

TESTING
Open through XAMPP, not by double-clicking the HTML files:
http://localhost/P1ToP4/login.html
Register a normal account. Promote your own test account to role='admin' using phpMyAdmin after the role column is created. Do not allow public registration to choose the admin role.
