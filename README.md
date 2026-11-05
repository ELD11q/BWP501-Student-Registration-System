# Student Registration System - BWP501

A website to manage student records using HTML, CSS, JavaScript, Bootstrap 5, PHP and MySQL.

## Features
- Register, login and logout (passwords saved with `password_hash()`, checked with `password_verify()`)
- Sessions to protect the student pages
- Add, view, update and delete students
- Upload a profile picture for each student (optional)
- Search by name, email or phone
- Sort by clicking the table headers
- Pagination (10 students per page) with Previous / Next
- AJAX validation for the student form
- Responsive design with Bootstrap

## Files
| File | Description |
|---|---|
| `index.html` | Home page |
| `login.html` | Login form |
| `register.html` | Register form |
| `login.php` | Checks the login and starts the session |
| `register.php` | Saves the new user |
| `logout.php` | Ends the session |
| `add_student.php` | Add student form and insert |
| `view_students.php` | Students list with search, sort and pagination |
| `update_student.php` | Edit student form and update |
| `delete_student.php` | Deletes a student |
| `validate_student.php` | Used by AJAX to check the form |
| `db.php` | Database connection and shared functions |
| `header.php` / `footer.php` | Navbar and footer for the PHP pages |
| `script.js` | JavaScript (messages, AJAX validation, delete confirm) |
| `style.css` | Styles |
| `database.sql` | Creates the database and tables |
| `uploads/` | Student pictures |

## Database
Database name: `student_registration_system`

**users**: id, username (unique), email (unique), password, created_at

**students**: id, name, email, phone, address, profile_picture, created_at

## How to run (XAMPP)
1. Install XAMPP and start Apache and MySQL.
2. Copy the project folder to `C:\xampp\htdocs\`.
3. Open `http://localhost/phpmyadmin`, click Import and choose `database.sql`.
4. Open `http://localhost/<folder name>/` in the browser.

The database settings are at the top of `db.php` (default: localhost, root, no password).

## How to run online
1. Create a MySQL database on the hosting (for example InfinityFree).
2. In phpMyAdmin open the SQL tab and run `database.sql` (without the first two lines).
3. Change the host, database name, user and password at the top of `db.php`.
4. Upload all files to the `htdocs` folder.

## How to use
1. Register a new account, then login.
2. Click **Add Student**, fill the form and choose a picture (optional).
3. Click **View Students** to see the list. Use the search box, click a header to sort, and use the pages at the bottom.
4. Click **Edit** to update a student or **Delete** to remove one (a confirm message appears first).
5. Click **Logout** when finished.

## How it works
- **Search**: `WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?` with prepared statements.
- **Pagination**: `COUNT(*)` gives the total, then `LIMIT 10 OFFSET (page - 1) * 10` gets one page.
- **AJAX validation**: `script.js` sends the form to `validate_student.php` with `fetch()`. The PHP file returns JSON with the errors and they are shown under each field without reloading the page. The same check runs again in PHP when saving.
- **File upload**: the picture is checked (type and size up to 2MB), renamed, moved to `uploads/` with `move_uploaded_file()`, and its path is saved in the `profile_picture` column.
- **Security**: PDO prepared statements against SQL injection, `htmlspecialchars()` against XSS, hashed passwords.

## Screenshots
See the `screenshots` folder.
