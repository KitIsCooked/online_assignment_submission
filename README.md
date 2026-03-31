# Online Assignment Submission System

A comprehensive web-based platform for managing student assignments and submissions, designed for educational institutions to streamline the assignment workflow.

## Features

- **Student Portal**: Submit assignments, view submission history, and track grades
- **Faculty Portal**: Create assignments, review submissions, provide feedback and grades
- **Admin Panel**: System administration and user management
- **Late Submission Tracking**: Automatic flagging of late submissions
- **File Upload Support**: Secure file upload with PDF support
- **Role-Based Access**: Secure authentication for students, faculty, and administrators

## Prerequisites

- **PHP 8.0+** (for backend functionality)
- **MySQL/MariaDB** (for database)
- **Apache/Nginx** (web server)
- **XAMPP/WAMP** (recommended for local development)

## Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/KitIsCooked/online_assignment_submission.git
cd online_assignment_submission
```

### 2. Database Setup
- Create a MySQL database named `assignment_portal`
- Import the database schema:
```bash
mysql -u root -p assignment_portal < db/assignment_portal.sql
```

### 3. Configuration
- Update database credentials in `config.php`:
```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // Your MySQL password
$DB_NAME = 'assignment_portal';
```

### 4. Web Server Setup
- Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP)
- Ensure the `uploads/` directory has write permissions
- Set up proper virtual host if needed

### 5. Access the Application
- Open your browser and navigate to `http://localhost/online_assignment_submission`
- Default admin credentials:
  - Username: `admin`
  - Password: `admin123` (change immediately after first login)

## Usage Instructions

### For Students
1. Register for a new account or login with existing credentials
2. View available assignments on the dashboard
3. Submit assignments before the due date
4. Track submission status and view feedback

### For Faculty
1. Login with faculty credentials
2. Create new assignments with due dates
3. Review and grade student submissions
4. Provide detailed feedback

## Project Structure

```
online_assignment_submission/
├── admin/                  # Admin panel files
├── assets/                 # CSS, JavaScript, and images
├── db/                     # Database schema
├── uploads/                # Student assignment uploads
├── config.php              # Database and system configuration
├── index.php               # Main entry point
├── student_login.php       # Student authentication
├── faculty_login.php       # Faculty authentication
├── student_dashboard.php   # Student main interface
├── faculty_dashboard.php   # Faculty main interface
└── ...                     # Additional functionality files
```

## Security Features

- Password hashing using PHP's built-in functions
- Session-based authentication
- SQL injection protection with prepared statements
- File upload security validation
- Role-based access control

## Demo Video

Watch the complete demonstration of the Online Assignment Submission System:

[🎥 Watch Demo on YouTube](https://www.youtube.com/watch?v=YOUR_DEMO_VIDEO_ID)

