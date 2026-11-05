# Student Registration System (BWP501 Project)

A dynamic, responsive website built with **HTML, CSS, JavaScript, Bootstrap 5, PHP and MySQL**.
Users can register, log in and manage student records (Create, Read, Update, Delete), with
profile picture uploads, search, sorting, pagination and AJAX form validation.

---

## 1. Features

| Requirement | Where |
|---|---|
| User registration (username, email, password + confirmation) | `register.html` → `register.php` |
| Login with username **or** email | `login.html` → `login.php` |
| Logout (session destroyed) | `logout.php` |
| Password hashing (`password_hash` / `password_verify`) | `register.php`, `login.php` |
| Session management / protected pages | `includes/auth.php` (`require_login()`) |
| Add student (+ optional profile picture) | `add_student.php` |
| View students (search, sort, pagination 10/page) | `view_students.php` |
| Update student (pre-filled form, change/remove picture) | `update_student.php` |
| Delete student with confirmation | `delete_student.php` + modal in the list |
| AJAX form validation | `assets/js/validation.js` → `validate_student.php` |
| File uploads with `move_uploaded_file()` | `includes/student_validation.php` |
| Responsive design (Bootstrap grid) | all pages + `assets/css/style.css` |
| SQL file | `database.sql` |

## 2. Project Structure

```
├── index.html               Home page (welcome + links)
├── login.html / login.php   Login form / login handler
├── register.html / register.php
├── logout.php
├── add_student.php          Add form + insert
├── view_students.php        List + search + sort + pagination
├── update_student.php       Edit form + update
├── delete_student.php       Confirmation + delete
├── validate_student.php     AJAX validation endpoint (JSON)
├── database.sql             Creates the database and tables
├── config.sample.php        Template for the database settings
├── includes/
│   ├── db.php               PDO connection
│   ├── auth.php             Sessions, login guard, CSRF, escaping, flash messages
│   ├── student_validation.php  Validation rules + upload handling
│   ├── student_form.php     Shared add/update form
│   ├── header.php / footer.php
├── assets/css/style.css
├── assets/js/auth.js        Login/register messages + validation
├── assets/js/validation.js  AJAX validation for student forms
├── assets/js/students.js    Live search, AJAX pagination/sorting, delete modal
├── uploads/                 Uploaded profile pictures
└── screenshots/             Screenshots of the website
```

## 3. Database Setup

Tables (see `database.sql`):

- **students**: `id` INT AUTO_INCREMENT PK, `name` VARCHAR(100), `email` VARCHAR(100), `phone` VARCHAR(15),
  `address` TEXT, `profile_picture` VARCHAR(255) (optional), `created_at` TIMESTAMP
- **users**: `id` INT AUTO_INCREMENT PK, `username` VARCHAR(100) UNIQUE, `email` VARCHAR(100) UNIQUE,
  `password` VARCHAR(255) (hash), `created_at` TIMESTAMP

### Option A: Online database (the project is configured for this)

1. Create a free MySQL database on a hosting provider (for example **InfinityFree**: Control Panel → *MySQL Databases*).
2. Open **phpMyAdmin** for that database → *Import* → choose `database.sql`.
   If the host already created the database for you, delete the first two lines
   (`CREATE DATABASE` and `USE`) before importing.
3. Copy `config.sample.php` to **`config.local.php`** and fill in the host, database name, user and password
   from your hosting panel. `config.local.php` is in `.gitignore`, so the password is never pushed to GitHub.
   (Environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SSL_CA` also work.)

### Option B: Local server (XAMPP / WAMP)

1. Install XAMPP and start **Apache** and **MySQL**.
2. Copy the project folder into `C:\xampp\htdocs\student_registration_system`.
3. Open `http://localhost/phpmyadmin` → *Import* → `database.sql`.
4. No config needed: the defaults are `localhost`, user `root`, empty password.
   (Or create `config.local.php` if your settings are different.)
5. Open `http://localhost/student_registration_system/`.

## 4. Running the Website Online (publishing)

1. Upload all project files to the host's `htdocs` folder (File Manager or FTP),
   including your `config.local.php`.
2. Make sure the `uploads/` folder is writable.
3. Open your site URL, for example `https://your-site.infinityfreeapp.com/`.

## 5. How to Use

1. **Register**: open *Register*, enter username, email, password and confirm it.
2. **Login**: use your username or email and password. Wrong details show an error message.
3. **Add Student**: fill name, email, phone, address and (optionally) choose a picture.
   Errors appear instantly under each field (AJAX) without reloading.
4. **View Students**: type in the search box to filter by name, email or phone; click column titles to sort;
   use *Previous / Next* or page numbers (10 students per page).
5. **Update**: click *Edit* on a row, change the data or picture, then *Save Changes*.
6. **Delete**: click *Delete*, confirm in the dialog.
7. **Logout**: click *Logout* in the navbar.

## 6. Code Explanation

### Authentication
- `register.php` validates the input again on the server, checks that the username and email are not
  already used, then stores `password_hash($password, PASSWORD_DEFAULT)`. The plain password is never saved.
- `login.php` finds the user by username **or** email and checks the password with `password_verify()`.
  On success it calls `session_regenerate_id(true)` and stores `user_id` and `username` in `$_SESSION`.
- Every student page starts with `require_login()` (in `includes/auth.php`), which redirects to the login
  page if there is no session. `logout.php` clears the session and its cookie and calls `session_destroy()`.

### Search and Pagination
- `view_students.php` builds `WHERE name LIKE :s OR email LIKE :s OR phone LIKE :s` with the search text
  wrapped in `%...%`, using bound parameters (no SQL injection).
- It first runs `SELECT COUNT(*)` to get the total, computes `totalPages = ceil(total / 10)`, then fetches
  only the current page with `LIMIT 10 OFFSET (page-1)*10`.
- Sorting uses a **whitelist** of column names (`id, name, email, phone, created_at`) and `ASC/DESC`,
  because column names cannot be bound as parameters.
- Client side, `students.js` sends the same URL with `&ajax=1`; the server returns only the table +
  pagination HTML, which replaces the old one. Live search is debounced (350 ms) and the browser URL is
  updated with the History API so refresh and Back still work. Without JavaScript, the links still work
  as normal page loads.

### AJAX Validation
- `validation.js` sends the text fields with `fetch()` to `validate_student.php` when a field loses focus,
  and again for the whole form on submit.
- `validate_student.php` runs the shared `validate_student()` function (required fields, email format with
  `FILTER_VALIDATE_EMAIL`, phone format, duplicate email) and returns JSON such as
  `{"valid": false, "errors": {"email": "Please enter a valid email address."}}`.
- The script shows each message under its field with Bootstrap's `is-invalid` style. The form is only
  really submitted when the server answers `valid: true`. The same validation runs again in
  `add_student.php` / `update_student.php`, so it cannot be bypassed.

### File Uploads
- The form uses `enctype="multipart/form-data"`. `handle_picture_upload()` checks the upload error code,
  limits the size to 2 MB, and detects the **real** file type with `finfo` (JPG, PNG, GIF, WEBP only).
- The file gets a random name (`student_<random>.jpg`) and is moved with `move_uploaded_file()` into
  `uploads/`. The relative path (`uploads/student_....jpg`) is saved in `students.profile_picture`.
- On update, a new picture replaces (and deletes) the old file; "Remove current picture" clears it.
  Deleting a student deletes the file too. `uploads/.htaccess` blocks running scripts from that folder.

### Security
- PDO prepared statements everywhere (`ATTR_EMULATE_PREPARES = false`) → SQL injection prevention.
- `htmlspecialchars()` (`e()` helper) on every output → XSS prevention.
- CSRF token on add, update and delete forms.
- Passwords hashed with `password_hash()`; session ID regenerated after login.
- Database credentials kept out of Git in `config.local.php`.

## 7. Screenshots

Put the screenshots in the `screenshots/` folder:
home, register, login, add student (with validation errors), view students (search + pagination),
update student, delete confirmation, and the mobile view.
