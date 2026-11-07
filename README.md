# El Gato's Academy

**Learn. Build. Grow.**

A full-stack e-learning website built with PHP and MySQL. It has a public marketing site, a student portal, and an admin dashboard for managing everything without touching code.

**Live demo:** https://elgatosacademy.kesug.com

## Features

### Public site
- Home, About, Courses, Instructors, Blog and Contact pages, all pulling live data from the database
- Course catalogue with category filter, sorting and keyword search
- Instructor profile pages with courses taught and real stats
- Blog with formatted (rich text) articles
- Application form for prospective students
- Fully responsive, including a mobile navigation menu

### Student portal
- Secure login with hashed passwords
- Dashboard with progress and hours tracking
- My Courses, profile editing with photo upload and password change
- Printable course completion certificates
- Logged-in state shown across the public site

### Admin dashboard
- Dashboard with live statistics and charts
- Review applications: approve or reject (approval creates the student account)
- Manage students, courses, instructors, enrollments, certificates and messages
- Blog manager with a rich text editor
- Change each page's hero image from the dashboard
- Image uploads for courses, instructors, blog posts and students

## Tech stack
PHP 8 (MySQLi prepared statements) · MySQL · HTML · CSS · JavaScript · Chart.js · Font Awesome

## Security notes
- Passwords stored with `password_hash()` / checked with `password_verify()`
- All database queries use prepared statements
- Role-separated sessions for admin and students
- Blog HTML is sanitised against a whitelist before saving and before display
- No credentials stored in the repository

## Author
Built by [Blaise](https://github.com/Blaise-boy) as a final web development project.
