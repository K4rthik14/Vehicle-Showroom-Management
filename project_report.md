# Project Report: Drivr — Vehicle Showroom Management System

**Course:** PCCST402 — Database Management System | S4 CSE | Group 7  
**College:** Government College of Engineering Kannur (GCEK)  
**Submitted to:** Dr. Nidheesh N  

---

## 1. Project Overview
- **Project Name:** Drivr — Vehicle Showroom Management System
- **Authors & Team members:**
  - **Karthik S** (Roll No: [Insert Roll No])
  - [Team Member 2] (Roll No: [Insert Roll No])
  - [Team Member 3] (Roll No: [Insert Roll No])
  - [Team Member 4] (Roll No: [Insert Roll No])

## 2. Abstract
Drivr is a modern web-based Vehicle Showroom Management System designed to digitize and streamline the core operational workflows of a contemporary automobile dealership. Traditionally, many small to mid-sized showrooms rely on fragmented manual records or simple spreadsheets, leading to data redundancy, stock inconsistencies, and slow reporting. This solution employs the **LAMP stack** (Linux, Apache, MariaDB, PHP) to centralize vehicle inventory tracking, customer relationship management, staff records, and sales transaction logs. 

The system features a secure, role-based authentication framework that distinguishes between Admin and Staff permissions. It implements key automated business logic such as real-time stock decrements upon sale completion, automatic "Sold" status updates, and a unique Employee ID generation system via database triggers. The outcome is a robust, scalable, and user-friendly platform that enhances data integrity, improves operational efficiency, and provides a clear audit trail for the showroom's commercial activities.

## 3. Problem Statement
The manual management of a vehicle showroom faces several critical challenges:
- **Data Redundancy & Errors:** Manual entry of customer and vehicle details often leads to duplicate or inconsistent data.
- **Inventory Mismatches:** Tracking vehicle stock (Available vs. Sold) manually is prone to errors, which can result in overselling or lost sales opportunities.
- **Inefficient Reporting:** Generating sales reports or revenue summaries from registers is time-consuming and unreliable.
- **Security Risks:** Physical registers are not password-protected and can be easily altered or lost.
- **Fragmentation:** No link between staff performance, sales records, and inventory status.

## 4. Objectives
- **Centralize Data:** Maintain a single relational database for all showroom operations.
- **Automate Workflows:** Implement automatic stock quantity updates and status changes.
- **Role-Based Security:** Ensure that sensitive staff and account management tasks are restricted to administrators.
- **Data Integrity:** Use foreign key constraints to ensure that every sale is validly linked to an existing vehicle, customer, and staff member.
- **Modern User Experience:** Provide a clean, responsive web interface for accessibility across different devices.

## 5. Tech Stack
| Component | Technology |
|---|---|
| **Operating System** | Linux (Ubuntu/CachyOS) |
| **Web Server** | Apache (httpd) |
| **Database** | MariaDB / MySQL |
| **Backend Language** | PHP 8.x |
| **Frontend Framework** | Bootstrap 5, Vanilla CSS |
| **Icons & Fonts** | Bootstrap Icons, Google Fonts (Inter, DM Mono) |
| **Environment Tooling** | Git, rsync, Shell Scripts |

## 6. System Architecture
The application follows a standard **Tiered Architecture**:
1. **Presentation Layer (Frontend):** HTML5, CSS3, and Bootstrap 5 provide a responsive UI. The Sidebar and Header are modularized for consistency.
2. **Logic Layer (PHP):** Handles server-side processing, input validation, session management, and role-based access control (RBAC). 
3. **Data Layer (MariaDB):** A relational database system storing tables for Customers, Vehicles, Sales, Staff, Suppliers, and Users.
4. **Web Server (Apache):** Acts as the mediator between the user's browser requests and the PHP backend.

## 7. Database Design

### Table Schemas
1. **STAFF:**
   - `Emp_ID` (VARCHAR(10), PK): Formatted ID (e.g., EMP001).
   - `Name` (VARCHAR(100)): Full name of the employee.
   - `Designation` (VARCHAR(50)): Job title.
   - `Contact` (VARCHAR(15)): Mobile number.
   - `DOJ` (DATE): Date of Joining.

2. **CUSTOMER:**
   - `Customer_ID` (INT, PK, AI): Unique ID.
   - `Fname` (VARCHAR(50)), `Lname` (VARCHAR(50)): Name details.
   - `Phone`, `Email`, `Address`: Contact information.

3. **SUPPLIER:**
   - `Supplier_ID` (INT, PK, AI): Unique supplier ID.
   - `S_Name`, `Phone_No`, `Email`: Supplier profile.

4. **VEHICLE:**
   - `VIN` (VARCHAR(20), PK): Vehicle Identification Number.
   - `Model_Name` (VARCHAR(100)): Model name.
   - `Status` (ENUM): 'Available' or 'Sold'.
   - `Fuel_type` (ENUM): Petrol, Diesel, Electric, etc.
   - `Stock_Quantity` (INT): Current inventory level.
   - `Supplier_ID` (FK): Origin supplier.

5. **SALE:**
   - `Sale_ID` (INT, PK, AI): Transaction ID.
   - `VIN` (FK), `Emp_ID` (FK), `Customer_ID` (FK): Links to core entities.
   - `Sale_Date`, `Amount`, `Payment_Method`: Transaction details.

6. **users:**
   - `user_id` (INT, PK): System user ID.
   - `password` (VARCHAR(255)): Bcrypt-hashed password.
   - `role` (ENUM): 'admin' or 'staff'.
   - `emp_id` (FK): Linked employee profile.

### ER Diagram Description
- **SUPPLIER** has a **one-to-many** relationship with **VEHICLE** (One supplier provides multiple car models).
- **STAFF** has a **one-to-many** relationship with **SALE** (A staff member can record multiple sales).
- **CUSTOMER** has a **one-to-many** relationship with **SALE** (A customer can purchase multiple vehicles).
- **VEHICLE** has a **one-to-many** relationship with **SALE** (A VIN is linked to a sales record).
- **STAFF** has a **one-to-one** relationship with **users** (Each staff member gets one login account).

### Normalization Proof
- **1NF:** Every column contains atomic values, and every row is unique identified by a primary key. No repeating groups exist in `STAFF`, `VEHICLE`, or `SALE`.
- **2NF:** The tables are in 1NF and all non-key attributes are fully functionally dependent on the primary key. (e.g., in `VEHICLE`, `Model_Name` depends on the unique `VIN`).
- **3NF:** The tables are in 2NF and have no transitive dependencies. Attributes like `Supplier_Name` are not stored in `VEHICLE`; instead, a `Supplier_ID` is used, ensuring that if a supplier’s phone number changes, it only needs updating in the `SUPPLIER` table.

## 8. Module-wise Feature Description
- **Login & Role Management:** Secure entry point using Employee ID. Admins have full access, while Staff have restricted views.
- **Dashboard:** At-a-glance view of total revenue, staff count, available vehicles, and recent sales activity.
- **Vehicle Inventory:** Comprehensive CRUD for vehicles, including tracking of fuel type and stock availability.
- **Customer Management:** Central directory for customer lead tracking and contact history.
- **Sales Management:** Point-of-Sale interface that triggers inventory updates and revenue logging.
- **Staff Management:** Admin-only module to track employee designations and contact info.
- **Supplier Management:** Directory of car manufacturers/suppliers providing stock to the showroom.
- **User Accounts:** Manage system access credentials and link them to employee profiles.

## 9. Security Implementation
- **Bcrypt Hashing:** All passwords are salted and hashed using `PASSWORD_DEFAULT` (Bcrypt) to prevent exposure if the database is compromised.
- **Prepared Statements:** Every database query involving user input uses MySQLi prepared statements and parameter binding (`bind_param`), effectively neutralizing **SQL Injection** attacks.
- **Session-based Authentication:** Secure sessions track user identity across pages; `session_start()` and `requireLogin()` ensure unauthorized users cannot access internal pages via direct URL.
- **Role-based Access Control (RBAC):** Admin users see "Edit/Add" forms for sensitive tables like Staff, while Staff users are presented with a readonly "Access Restricted" message.

## 10. Key Business Logic
- **Stock Decrement:** When a sale is recorded, the system automatically subtracts `1` from the `Stock_Quantity` of the selected VIN.
- **Auto-Update Status:** If a vehicle’s stock reaches `0`, its status is automatically flipped from "Available" to "Sold".
- **Stock Restoration:** Deleting a sales entry (Admin action) automatically increments the vehicle stock back, ensuring inventory accuracy.
- **Trigger Logic:** A MySQL Trigger (`before_staff_insert`) auto-generates sequential IDs (EMP001, EMP002, etc.) to ensure a standard corporate format.

## 11. Screenshots (Placeholder Section)
- **[Login Screen]:** Modern split-layout with gradient background.
- **[Dashboard]:** Clean white cards with vertical accent borders for stats.
- **[Inventory Table]:** Detail-rich table with badges for Status and Fuel Type.
- **[Sales Form]:** Real-time dropdowns for selecting vehicles and customers.

## 12. Challenges & Solutions
1. **Challenge:** Migrating from plain integers to alphanumeric Employee IDs.
   - **Solution:** Altered schemas to `VARCHAR(10)` and implemented a DB trigger for manual sequence handling.
2. **Challenge:** Handling MySQL Strict Mode for auto-generating Primary Keys.
   - **Solution:** Modified the PHP insertion logic to pass an empty string, allowing the `BEFORE INSERT` trigger to intercept and provide the value.
3. **Challenge:** Data truncation in ENUM fields during sales logging.
   - **Solution:** Identified mismatch in `bind_param` types where strings were being treated as integers; corrected these to preserve data integrity.

## 13. Conclusion
The "Drivr" project successfully digitalizes the management requirements of a vehicle showroom. By applying rigorous DBMS principles such as 3NF, Foreign Key constraints, and secure transaction logic, the platform provides a reliable foundation for business growth. The development process reinforced core competencies in full-stack LAMP development, specifically focusing on the intersection of business logic and relational database integrity.

## 14. References
- [PHP Documentation](https://www.php.net/docs.php)
- [MariaDB Knowledge Base](https://mariadb.com/kb/en/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.0/getting-started/introduction/)
- [W3Schools SQL Tutorial](https://www.w3schools.com/sql/)
