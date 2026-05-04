# Integrated Web Based Information System for Youth Profiling and Skills Matching

A complete implementation of the Integrated Web Based Information System for Youth Profiling and Skills Matching using **PHP (OOP), MySQL, Tailwind CSS, HTML, CSS, and JavaScript**.

## 🚀 Features

- ✅ **Object-Oriented PHP Design** - Clean, modular, and scalable architecture
- ✅ **MySQL Database** - Normalized schema with sample data included
- ✅ **Tailwind CSS Styling** - Modern, responsive UI matching the original design
- ✅ **User Authentication** - Secure login system with role-based access
- ✅ **youth Profile Management** - Create, read, update, delete youth profiles
- ✅ **Opportunity Management** - Job openings, training programs, scholarships
- ✅ **Skills Matching Engine** - Matches youth with opportunities
- ✅ **Notifications System** - Broadcasting notifications to matched candidates
- ✅ **Reports & Analytics** - Dashboard with key metrics and statistics
- ✅ **Responsive Design** - Works on desktop and mobile devices

## 📋 System Requirements

- **PHP 7.4+** or higher
- **MySQL 5.7+** or MariaDB
- **XAMPP** (recommended) or any PHP/Apache server
- **Web Browser** with modern JavaScript support

## 🔧 Installation & Setup

### Step 1: Extract Files

Place the `youth_db` folder in your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\youth_db
```

### Step 2: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `civic_horizon_youth`
3. Import the SQL dump:
   - Go to **Import** tab
   - Select `database_dump.sql` file from the `youth_db` folder
   - Click **Go**

**OR use command line:**

```bash
mysql -u root -p < database_dump.sql
```

### Step 3: Configure Database Connection

Edit `config/database.php` and update credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Add password if set
define('DB_NAME', 'civic_horizon_youth');
```

### Step 4: Start Apache & MySQL

- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### Step 5: Access the Application

Visit: `http://localhost/youth_db`

## 👤 Default Login Credentials

### Admin Accounts (4 Accounts Available)

| Username | Password  | Role  | Status |
| -------- | --------- | ----- | ------ |
| admin1   | Admin@123 | Admin | Active |
| admin2   | Admin@456 | Admin | Active |
| admin3   | Admin@789 | Admin | Active |
| admin4   | Admin@999 | Admin | Active |

### Staff Accounts

| Username | Password  | Role    |
| -------- | --------- | ------- |
| jsmith   | Staff@123 | Staff   |
| mgarcia  | Staff@123 | Staff   |
| rsantos  | Staff@123 | Manager |

## 📦 Database Schema

### Tables

1. **users** - System users (admin, staff, managers)
2. **youth_profiles** - Out-of-School Youth profiles with skills
3. **opportunities** - Job openings, training, scholarships
4. **youth_matches** - Matching between youth and opportunities
5. **notifications** - System notifications and broadcasts

## 🏗️ Project Structure

```
youth_db/
├── Classes/
│   ├── Database.php          # Database connection & operations
│   ├── User.php              # User authentication & management
│   ├── youthProfile.php        # youth profile CRUD operations
│   ├── Opportunity.php       # Opportunity management
│   ├── Matching.php          # Skills matching algorithm
│   ├── Notification.php      # Notification system
│   ├── Report.php            # Report generation
│   └── Dashboard.php         # Dashboard statistics
├── config/
│   └── database.php          # Database configuration
├── pages/
│   ├── login.php             # Login page
│   ├── dashboard.php         # Main dashboard
│   ├── profiles.php          # youth profiles list
│   ├── opportunities.php     # Opportunities management
│   ├── matching.php          # Skills matching interface
│   ├── notifications.php     # Notifications management
│   ├── reports.php           # Reports & analytics
│   ├── settings.php          # User settings
│   └── logout.php            # Logout handler
├── includes/
│   ├── header.php            # Page header with sidebar
│   └── footer.php            # Page footer
├── api/                       # API endpoints (for future use)
├── assets/                    # Static files (for future use)
├── init.php                   # Application initializer
├── index.php                  # Entry point
└── database_dump.sql         # SQL dump with schema & sample data
```

## 🔑 OOP Classes

### Database Class

Handles all database connections and queries with prepared statements for security.

```php
$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

### User Class

Manages authentication, registration, and user operations.

```php
$user = new User($database);
$result = $user->login('admin1', 'Admin@123');
```

### youthProfile Class

CRUD operations for youth profiles with filtering.

```php
$youth = new youthProfile($database);
$profiles = $youth->getAll(['status' => 'Active']);
```

### Opportunity Class

Manages job opportunities, training, and scholarships.

```php
$opportunity = new Opportunity($database);
$opps = $opportunity->getAll(['type' => 'Job Opening']);
```

### Matching Class

Skills-based matching algorithm between youth and opportunities.

```php
$matching = new Matching($database);
$score = $matching->calculateMatchScore($youth_id, $opp_id);
```

### Dashboard Class

Provides statistics and analytics for the dashboard.

```php
$dashboard = new Dashboard($database);
$stats = $dashboard->getStats();
```

## 📊 Sample Data Included

- **12 youth Profiles** with various skills (Welding, Culinary, IT, etc.)
- **8 Opportunities** including jobs, training, and scholarships
- **15 Skill Matches** with scoring
- **6 Notifications** sent to users
- **9 User Accounts** (4 admins + 5 staff)

## 🎨 Features Implemented

### Dashboard

- Summary statistics cards
- Skill distribution chart
- Status breakdown (Active/In Training/Employed)
- Recent registrations table

### youth Profiles

- List all profiles with filters
- Search by name/email
- Filter by barangay, gender, education, status
- View profile details
- Add/Edit/Delete profiles

### Opportunities

- View all opportunities
- Filter by type (Job/Training/Scholarship)
- Manage deadline and slots
- Track filled slots

### Skills Matching

- Intelligent matching algorithm (0-100 score)
- Filter by minimum match score
- Broadcast to matched candidates
- Accept/Reject matches

### Notifications

- Send notifications to youth
- Broadcast system messages
- Track notification status
- Archive notifications

### Reports

- youth Statistics
- Opportunity Analysis
- Matching Success Rate
- Employment Metrics
- Export to CSV

## 🔐 Security Features

- Password hashing with bcrypt
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control
- Input validation and sanitization

## 🎯 Future Enhancements

- [ ] Email notifications
- [ ] SMS integration
- [ ] Image upload for profiles
- [ ] Advanced analytics
- [ ] Mobile app integration
- [ ] Real-time notifications
- [ ] Payment gateway integration
- [ ] Automated matching algorithm

## 📧 Support & Contact

For issues or questions, please contact the development team.

---

**Created**: April 2024  
**Version**: 1.0  
**License**: MIT
