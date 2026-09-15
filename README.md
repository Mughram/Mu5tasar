# Mu5tasar

Mu5tasar is a simple quiz platform where every quiz has exactly **five questions**. Users can create an account, build multiple-choice quizzes, and test their knowledge with an instant score.

## About the project

We built Mu5tasar as a **group mini-project for our college Web Programming course** at Jazan University. It brought together what we learned about PHP, databases, and common web vulnerabilities in one practical application.

The project gave us experience handling forms, managing PHP sessions, connecting an application to a database, and applying protections against SQL injection, cross-site scripting (XSS), and cross-site request forgery (CSRF).

## Features

- Register and log in using PHP session authentication.
- Create a quiz with five questions, four options per question, and one correct answer.
- Add an optional image to each question.
- Browse quizzes, submit answers, and see a score out of five.

## Built with

- **PHP** for application logic, forms, sessions, and authentication.
- **PostgreSQL on Supabase** for quiz and account data, accessed through server-side PDO.
- **HTML, CSS, and Bootstrap** for the interface.

The database connection was adapted from MySQL to PostgreSQL while keeping the existing PHP structure and authentication system.

## Security practices

- Parameterized PDO statements keep user input separate from SQL.
- Output escaping protects HTML text and attributes from XSS.
- Session-bound CSRF tokens protect form submissions and logout.
- Passwords are hashed with `password_hash()` and verified with `password_verify()`.
- Image uploads are validated by content and saved with random filenames.
- Database credentials stay in server environment variables, and database connections require TLS.

## Run locally

1. Install PHP 8.0+ with `pdo_pgsql`, `fileinfo`, and sessions enabled.
2. Configure the database environment variables described in [`.env.example`](.env.example). This file is a reference; the application does not automatically load `.env` files.
3. Follow [SETUP.md](SETUP.md) for database connection settings, TLS configuration, and hosting details.
4. For local HTTP development only, set `SESSION_COOKIE_SECURE=0`, then start the server:

```sh
php -S 127.0.0.1:8080 -t main
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080). Use HTTPS and `SESSION_COOKIE_SECURE=1` in production.

The repository does not include database passwords or account records. A configured database is required for login, registration, and quiz operations.

## Verification

```sh
php tests/security.php
node tests/http-security.mjs
php tests/database.php
```

The template tests require PHP DOM; the HTTP tests require Node.js 20+. The database check requires private connection settings. See [SETUP.md](SETUP.md) for prerequisites and verification status.

## Our team

- Mughram Ayshi
- Rayan Hakami
- Alaihm Ayel
- Mohammad Holbah

Jazan University, College of Engineering and Computer Science.
