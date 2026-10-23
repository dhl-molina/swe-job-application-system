# Software Engineer Job Application Management System

A small PHP and PDO web application for managing software engineer job applications.
It has an application form, a table of every submitted application, and the ability to
edit and delete records (CRUD). Built as a college programming assignment. There is no
login or user management, as the assignment specifies.

## Features

- **Create**: application form with server-side validation.
- **Read**: table of all applications, newest first.
- **Update**: pre-filled edit form that saves to the chosen record only.
- **Delete**: confirmation page, then an HTTP POST removes exactly one record.
- Success and error messages, form values kept after a validation error, and friendly
  handling of invalid record IDs and database errors.

## Tech stack

PHP 8 (PDO with prepared statements), MariaDB / MySQL, plain HTML and CSS.
No framework and no JavaScript. Written for the PHP 8.2 and MariaDB 10.4 bundled with XAMPP 8.2.12.

## Database

Database `swe_job_applications_db`, table `swe_applicants` (11 columns):

| Column | Type | Notes |
| --- | --- | --- |
| `id` | INT, AUTO_INCREMENT | Primary key |
| `first_name` | VARCHAR(100) | Required |
| `last_name` | VARCHAR(100) | Required |
| `email` | VARCHAR(150) | Required, unique |
| `phone` | VARCHAR(30) | Required |
| `education` | VARCHAR(150) | Required |
| `technical_skills` | TEXT | Required |
| `experience_level` | VARCHAR(50) | Internship, Entry-Level, or Experienced |
| `portfolio_url` | VARCHAR(255) | Optional (NULL when empty) |
| `github_url` | VARCHAR(255) | Required |
| `date_added` | DATETIME | Set automatically to the current time |

The full script is in [`sql/schema.sql`](sql/schema.sql). It also inserts three fictional sample applicants.

## Setup (XAMPP on Windows)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this project folder to `C:\xampp\htdocs\swe_job_application_system\`
   (`index.php` must sit directly inside that folder).
3. Open `http://localhost/phpmyadmin`, go to **Import**, choose `sql/schema.sql`, and click **Go**.
   This creates the database, the table, and the sample rows.
4. Open `http://localhost/swe_job_application_system/` in your browser.

### Configuration

`core/dbConfig.php` uses the XAMPP defaults (host `localhost`, user `root`, empty password).
If your setup differs, create `core/config.local.php` and override the values you need:

```php
<?php
$dbUser = 'your-user';
$dbPass = 'your-password';
$host   = 'localhost;port=3307';   // only if MySQL is not on port 3306
```

`core/config.local.php` is listed in `.gitignore`, so real credentials are never committed.

## Project structure

```
swe_job_application_system/
├── core/
│   ├── dbConfig.php        PDO connection and session start
│   ├── handleForms.php     validates POST requests, calls models.php, redirects
│   └── models.php          all SQL (PDO prepared statements) and small helpers
├── sql/
│   └── schema.sql          database, table, and sample data
├── styles/
│   └── styles.css          navy, white, and light-gray stylesheet
├── index.php               application form + table of all applications
├── editapplicant.php       edit form for one application
├── deleteapplicant.php     delete confirmation page
├── .gitignore
└── README.md
```

## Validation rules

- Names: letters, spaces, periods, apostrophes, and hyphens only (max 100 characters).
- Email: valid format and not already used by another application.
- Phone: digits, spaces, parentheses, hyphens, and an optional leading `+`; 7 to 15 digits.
- Technical skills: up to 1000 characters.
- Experience level: one of the three dropdown values.
- Portfolio URL (optional): a valid `http://` or `https://` address.
- GitHub URL: must look like `https://github.com/your-username`.

## Security notes

- Every query that uses user input is a PDO prepared statement.
- All user-provided values are escaped with `htmlspecialchars()` before being displayed.
- Deleting requires an HTTP POST. Opening the delete page never deletes anything.
- Database details are logged on the server and never shown to visitors.
- Not included: authentication and CSRF tokens. They are outside the scope of this assignment.

## Author

David Hans Lancelotte M. Molina · UCOS 3-1 · Ivan Duane Dequito
