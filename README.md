# Vehicle-Showroom-Management

🚗 Vehicle Showroom Management System (LAMP Stack)

A web-based Vehicle Showroom Management System developed using the LAMP stack (Linux, Apache, MySQL/MariaDB, PHP).
The system digitalizes showroom operations such as vehicle inventory management, customer handling, sales, and staff records.

## 📌 Project Overview

Managing a vehicle showroom involves handling multiple operations like tracking available vehicles, managing customer records, recording sales and payments, and managing staff.
This project provides a centralized web application to efficiently manage all these activities using a relational database.

The system is designed with real-world business workflows in mind and follows proper DBMS normalization principles.

## 🛠️ Tech Stack

| Layer            | Technology                |
|------------------|---------------------------|
| Operating System | CachyOS (Arch-based Linux)|
| Web Server       | Apache (httpd)            |
| Backend          | PHP 8.x                  |
| Database         | MariaDB                   |
| Frontend         | HTML, CSS, Bootstrap 5    |
| Architecture     | LAMP                      |

## 🎯 Features

### 🔹 Vehicle Inventory
- Add, edit, and delete vehicles
- Track stock quantity and status (Available/Sold)
- Link vehicles with suppliers

### 🔹 Customer Management
- Store customer personal and contact details
- Edit and delete customer records

### 🔹 Sales Management
- Record vehicle sales with date, amount, and payment method
- Automatic stock update on sale
- Restore stock on sale deletion

### 🔹 Staff Management
- Manage showroom staff records
- Track designation and date of joining

### 🔹 Supplier Management
- Manage vehicle suppliers
- View vehicle count per supplier

### 🔹 User Accounts
- Admin and staff roles
- Session-based authentication
- Admin-only user management

## 📂 Project Structure

```
Vehicle-Showroom-Management/
│
├── includes/               # Shared PHP components
│   ├── auth.php            # Authentication & role checks
│   ├── db.php              # Database connection config
│   ├── header.php          # Shared layout (sidebar, nav, CSS)
│   ├── footer.php          # Page footer + scripts
│   └── logout.php          # Session logout handler
│
├── pages/                  # Application pages
│   ├── dashboard.php       # Dashboard with stats & recent sales
│   ├── vehicles.php        # Vehicle CRUD
│   ├── customers.php       # Customer CRUD
│   ├── sales.php           # Sales management
│   ├── staff.php           # Staff CRUD
│   ├── suppliers.php       # Supplier CRUD
│   └── users.php           # User account management (admin)
│
├── index.php               # Login page (entry point)
├── vsms_setup.sql          # Database schema + seed data
├── setup.sh                # Automated setup script for CachyOS
└── README.md
```

## ⚙️ Installation & Setup (CachyOS / Arch Linux)

### Prerequisites

Ensure these packages are installed:

```bash
sudo pacman -S apache php php-apache mariadb
```

Enable and start the services:

```bash
sudo systemctl enable --now httpd
sudo systemctl enable --now mariadb
sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
```

Configure PHP with Apache — edit `/etc/httpd/conf/httpd.conf`:

```apache
LoadModule php_module modules/libphp.so
Include conf/extra/php_module.conf
```

Enable mysqli in `/etc/php/php.ini`:

```ini
extension=mysqli
```

Restart Apache:

```bash
sudo systemctl restart httpd
```

### Quick Setup

Run the setup script from the project directory:

```bash
chmod +x setup.sh
./setup.sh
```

This will:
1. ✅ Check that Apache and MariaDB are running
2. ✅ Create the `vsms` database
3. ✅ Create a dedicated `vsms_user` with password authentication
4. ✅ Import the schema and seed data
5. ✅ Deploy the project to `/srv/http/vsms/`
6. ✅ Update database credentials

### Manual Setup

If you prefer manual setup:

1. **Create the database user:**
   ```bash
   sudo mariadb -e "
       CREATE DATABASE IF NOT EXISTS vsms CHARACTER SET utf8mb4;
       CREATE USER IF NOT EXISTS 'vsms_user'@'localhost' IDENTIFIED BY 'vsms_pass_2026';
       GRANT ALL PRIVILEGES ON vsms.* TO 'vsms_user'@'localhost';
       FLUSH PRIVILEGES;
   "
   ```

2. **Import the schema:**
   ```bash
   mariadb -u vsms_user -p'vsms_pass_2026' vsms < vsms_setup.sql
   ```

3. **Deploy to Apache document root:**
   ```bash
   sudo cp -r . /srv/http/vsms
   sudo chown -R http:http /srv/http/vsms
   ```

4. **Open in browser:**
   ```
   http://localhost/vsms/
   ```

### Login Credentials

| Username | Password   | Role  |
|----------|------------|-------|
| admin    | admin123   | Admin |
| staff1   | staff123   | Staff |

## 🗄️ Database Design

The database uses a normalized relational schema with the following tables:

| Table     | Purpose                          |
|-----------|----------------------------------|
| users     | Login credentials and roles      |
| CUSTOMER  | Customer personal details        |
| STAFF     | Employee records                 |
| SUPPLIER  | Vehicle supplier information     |
| VEHICLE   | Vehicle inventory                |
| SALE      | Sales transactions               |

### Key Relationships

- One **supplier** can supply many **vehicles**
- A **customer** can purchase multiple **vehicles**
- A **staff member** handles **sales**
- Each **sale** links a vehicle, customer, and staff member

## 🔐 Security Considerations

- Prepared statements to prevent SQL injection
- Password hashing with `password_hash()` / `password_verify()`
- Session-based authentication with role checks
- HTML output escaping with `htmlspecialchars()`
- Dedicated database user (not root)

## ⚠️ CachyOS / Arch Notes

- Apache document root is `/srv/http/` (not `/var/www/html/`)
- MariaDB uses unix_socket authentication for root — PHP cannot connect as root with empty password
- The setup script creates a dedicated `vsms_user` with password-based authentication
- PHP module is loaded via `libphp.so` (not `mod_php`)

## 📈 Future Enhancements

- Role-based access control (expand beyond admin/staff)
- Analytics dashboard with charts
- Invoice/receipt generation
- Email/SMS notifications
- REST API support
- Vehicle booking system

## 👨‍💻 Author

Karthik S
B.Tech CSE
