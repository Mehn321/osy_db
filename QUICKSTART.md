# 🚀 QUICK START GUIDE - Municipal KK Profiling System

## 5-Minute Setup

### 1️⃣ Copy Files

```
C:\xampp\htdocs\osy_db  ← Already created with all files
```

### 2️⃣ Import Database

**Option A: Using phpMyAdmin**

- Go to: `http://localhost/phpmyadmin`
- Create new database: `municipal_kk_profiling`
- Click Import → Select `database_dump.sql` → Go

**Option B: Using Command Line**

```bash
mysql -u root -p < "C:\xampp\htdocs\osy_db\database_dump.sql"
```

### 3️⃣ Start Services

- Open XAMPP Control Panel
- Click **Start** on Apache & MySQL

### 4️⃣ Access Application

```
http://localhost/osy_db
```

---

## 🔐 Login Credentials

### Admin Accounts (Choose Any)

```
Username: admin1    Password: Admin@123
Username: admin2    Password: Admin@456
Username: admin3    Password: Admin@789
Username: admin4    Password: Admin@999
```

### Staff Accounts

```
Username: jsmith    Password: Staff@123
```

---

## ✨ What's Built

✅ **8 OOP Classes** - Database, User, OSYProfile, Opportunity, Matching, Notification, Report, Dashboard  
✅ **9 Pages** - Login, Dashboard, Profiles, Opportunities, Matching, Notifications, Reports, Settings  
✅ **12 OSY Profiles** - Sample data with varied skills  
✅ **8 Opportunities** - Jobs, training programs, scholarships  
✅ **100% Responsive** - Works on desktop, tablet, mobile  
✅ **Secure** - Password hashing, prepared statements, session authentication

---

## 🎯 Features to Explore

1. **Dashboard** - View statistics and trends
2. **Profiles** - List OSY with filtering options
3. **Opportunities** - Job openings and training programs
4. **Matching** - Skills-based matching with scores
5. **Notifications** - System messages and broadcasts
6. **Reports** - Analytics and statistics

---

## 📝 Database Structure

- **users** - Admin & staff accounts
- **osy_profiles** - Youth profiles with skills
- **opportunities** - Jobs, training, scholarships
- **osy_matches** - Skills matching results
- **notifications** - System notifications

---

## 🛠️ Troubleshooting

**Q: Can't access the site?**

- Make sure Apache & MySQL are running in XAMPP
- Check if database was imported successfully
- Verify database name is `civic_horizon_osy`

**Q: Login doesn't work?**

- Try exactly: `admin1` / `Admin@123`
- Check database connection in `config/database.php`

**Q: Database import failed?**

- Check if database `civic_horizon_osy` exists
- Verify MySQL is running
- Try importing via command line

---

## 📋 System Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache (included with XAMPP)
- Modern web browser

---

## 🎨 Technology Stack

- **Backend**: PHP 8.0+ (OOP)
- **Database**: MySQL
- **Frontend**: HTML5
- **Styling**: Tailwind CSS
- **UI Components**: Material Symbols
- **Charts**: Chart.js

---

## 💾 Files Included

```
osy_db/
├── Classes/           ← 8 OOP PHP classes
├── config/            ← Database configuration
├── pages/             ← 9 application pages
├── includes/          ← Header/footer layouts
├── init.php          ← Application initializer
├── index.php         ← Entry point
├── database_dump.sql ← Complete database with sample data
└── README.md         ← Full documentation
```

---

## 🎓 Sample Data Included

✨ **12 OSY Profiles**

- Ricardo Santos (Automotive specialist)
- Maria Elena Dela Cruz (Culinary expert)
- Roberto Garcia (IT Support)
- Patricia Lozano (Hospitality)
- And 8 more with various skills

💼 **8 Opportunities**

- TESDA NCII Cookery Training
- Logistics Assistant Job
- STEM University Grant
- Welding Specialist Training
- BPO Customer Service
- Automotive Technician
- And more...

👥 **9 User Accounts**

- 4 Admin accounts
- 5 Staff accounts with different roles

📊 **15 Matches**

- Ranging from 73%-94% match scores
- Various statuses (Pending, Accepted, etc.)

---

## 🔄 Next Steps

1. ✅ Import database
2. ✅ Start Apache & MySQL
3. ✅ Login at `localhost/osy_db`
4. ✅ Explore all pages
5. ✅ Test filtering and features

---

## 📚 Documentation Files

- `README.md` - Complete documentation
- `database_dump.sql` - Database schema and sample data
- This file - Quick start guide

---

## ⚡ Performance Notes

- Optimized queries with indexes
- Clean OOP architecture
- Prepared statements for security
- Responsive Tailwind CSS design
- Lightweight and fast loading

---

## 🎉 You're All Set!

The application is fully functional and ready to use. All pages are connected, database is populated with sample data, and everything is styled with Tailwind CSS.

**Happy coding! 🚀**
