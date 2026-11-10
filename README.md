# NexaTech HR — Employee Management System

A web-based Employee Management System developed using PHP and MySQL. The application runs locally using XAMPP (Apache, PHP, and MySQL) and uses Tailwind CSS v4 for styling.

## Features

- User authentication and Role-Based Access Control (RBAC).
- Employee Management (Add, View, Edit, Delete).
- Department Management.
- Attendance Management.
- Leave Management (Submit and Approve Requests).
- Salary Management.
- User Account and Role Management.
- Employee Profile Management.
- Employee Account Linking.

## User Roles and Permissions

| Role            | Permissions                                                                                                                                                   |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Super Admin** | Full system access. Can manage all users, roles, employees, departments, and HR operations, including other Admin and Super Admin accounts.                   |
| **Admin**       | Manages employees, departments, attendance, leaves, and salaries. Can create Admin, HR Manager, and Employee accounts but cannot modify Super Admin accounts. |
| **HR Manager**  | Manages employees, attendance, leaves, and salaries. Can create Employee accounts only.                                                                       |
| **Employee**    | Can view their own profile, attendance, and salary information and submit leave requests.                                                                     |

## Technologies Used

- PHP 8+
- MySQL / MariaDB
- HTML
- Tailwind CSS v4

- XAMPP

## Requirements

- XAMPP
- PHP 8 or later
- MySQL or MariaDB
- A modern web browser

## Installation and Setup

### 1. Start XAMPP

Open the XAMPP Control Panel and start Apache and MySQL.

### 2. Copy the Project

Copy the project folder into the XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\EMS
```

### 3. Create the Database

1. Open [phpMyAdmin](http://localhost/phpmyadmin).
2. Create a database named `employee_management`.
3. Navigate to the Import tab.
4. Select `database/employee_management.sql`.
5. Click Go to import the database.

### 4. Configure Database Connection

Open `DataBaseConnetoin.php` and configure your database credentials if necessary.

```php
$host = "localhost:3306";
$user = "root";
$pass = "";
$database = "employee_management";
```

### 5. Run the Application

Open your browser and visit:

```text
http://localhost/EMS/Login.php
```

## Demo Accounts

| Role        | Email                     |
| ----------- | ------------------------- |
| Super Admin | `SuperAdmin123@gmial.com` |
| HR Manager  | `HRadmin@gmail.com`       |
| Employee    | `Employee@gmial.com`      |

Passwords are configured locally and are not provided in this repository.

## Employee Profile Linking

Each Employee account must be linked to an employee record through the `users.employee_id` column.

- New employee accounts can be linked during registration.
- `CreateEmployeeAccount.php` automatically links accounts to employee records.
- Existing accounts can be linked through the Users & Roles page.
- Each employee record can be associated with only one user account.

Once linked, employees can access their complete profile, attendance, and salary information.

## Project Structure

```text
EMS/
│
├── index.php
├── Login.php
├── Register.php
├── Dashboard.php
├── AuthCheck.php
│
├── AddEmployee.php
├── EditEmployee.php
├── ViewEmployees.php
├── DeleteEmployee.php
│
├── AddDepartment.php
├── EditDepartment.php
├── ViewDepartments.php
├── DeleteDepartment.php
│
├── Attendance.php
├── LeaveManagement.php
├── SalaryManagement.php
├── MyProfile.php
├── CreateEmployeeAccount.php
│
├── includes/
│   └── UserManagement.php
│
└── database/
    └── employee_management.sql
```

## Security Notes

- Do not upload database passwords or sensitive credentials to GitHub.
- Remove real employee information and confidential records from the database backup before publishing.
- User passwords should be securely hashed.
- Use strong passwords for all accounts.
- For production environments, use a locally built Tailwind CSS version instead of the CDN.

## License

This project was developed for educational and coursework purposes.
