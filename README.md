# College Registration System
## Author: Miso
### Setup Instructions

### Requirements
- PHP 7.4+ (with GD extension)
- MySQL 5.7+ / MariaDB
- Apache / Nginx with mod_rewrite

### Installation

1. **Copy files** to your web server root (e.g. `htdocs/college/` or `/var/www/html/college/`)

2. **Create the database:**
   - Open phpMyAdmin or MySQL CLI
   - Run the contents of `includes/schema.sql`

3. **Configure DB connection** in `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'college_registration');
   ```

4. **Set permissions:**
   ```bash
   chmod 775 uploads/photos/
   ```

5. **Visit** `http://localhost/college/` in your browser.

---

## Default Credentials

| Role    | Email                  | Password    |
|---------|------------------------|-------------|
| Admin   | admin@kiitec.tz       | password    |
| Student | (any you add)          | Student@123 |

> Students are **forced to change their password** on first login.

---

## Features

### Admin
- **Dashboard** — stats: total students, courses, year breakdown
- **Add Student** — with photo upload, course, type, year
- **View Students** — grouped by Full-Time / Short Course, further by course
- **Edit Student** — inline modal editor with all fields
- **Delete Student** — with confirmation

### Student
- **View Profile** — read-only view of all details
- **Edit Profile** — update email, phone, photo
- **Change Password** — mandatory on first login, optional anytime

---

## Directory Structure
```
college/
├── index.php               Login page
├── logout.php
├── includes/
│   ├── config.php          DB connection & helpers
│   └── schema.sql          Database setup
├── assets/
│   ├── css/style.css       Global styles
│   └── img/default.png     Fallback avatar
├── uploads/photos/         Student photo uploads
├── admin/
│   ├── sidebar.php
│   ├── dashboard.php
│   ├── students.php        List / Edit / Delete
│   └── add_student.php
└── student/
    ├── sidebar.php
    ├── profile.php
    ├── edit_profile.php
    └── change_password.php
```
