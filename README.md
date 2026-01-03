# Vehicle-Showroom-Management

🚗 Vehicle Showroom Management System (LAMP Stack)

A web-based Vehicle Showroom Management System developed using the LAMP stack (Linux, Apache, MySQL, PHP).
The system digitalizes showroom operations such as vehicle inventory management, customer handling, bookings, sales, payments, and service records.

📌 Project Overview

Managing a vehicle showroom involves handling multiple operations like tracking available vehicles, managing customer bookings, recording sales and payments, and maintaining post-sale service history.
This project provides a centralized web application to efficiently manage all these activities using a relational database.

The system is designed with real-world business workflows in mind and follows proper DBMS normalization principles.

🛠️ Tech Stack
Layer	Technology
Operating System	Linux
Web Server	Apache
Backend	PHP
Database	MySQL
Frontend	HTML, CSS, JavaScript
Architecture	LAMP
🎯 Features
🔹 Inventory Management

Add and manage vehicle details

Track available stock

Link vehicles with suppliers

🔹 Customer Management

Store customer personal and contact details

Maintain government ID information

🔹 Vehicle Booking

Allow customers to book vehicles

Track booking status (Booked / Cancelled / Converted)

🔹 Sales Management

Record vehicle sales

Handle walk-in and booking-based sales

Assign sales staff to each sale

🔹 Payment Management

Support advance and partial payments

Track payment methods and payment history

🔹 Service Management

Service booking for vehicles

Record service details and costs

Track next service due date

🔹 Staff Management

Manage showroom staff

Assign staff to sales and services

🗄️ Database Design

The database is designed using a normalized relational schema with the following core entities:

Supplier

Vehicle

Customer

Staff

Vehicle_Booking

Sales

Payment

Service_Booking

Service

The design ensures:

Data consistency using primary and foreign keys

One-to-many relationships where applicable

Separation of concerns (sales vs payments, booking vs service)

🔗 Entity Relationship Summary

One supplier can supply many vehicles

A customer can book and purchase multiple vehicles

A sale can have multiple payments

Staff members handle sales and services

Service records are maintained for customer-owned vehicles

⚙️ Installation & Setup
1️⃣ Prerequisites

Linux OS

Apache Web Server

MySQL Server

PHP (7.x or above)

Browser (Chrome / Firefox)

2️⃣ Clone the Repository
git clone https://github.com/your-username/vehicle-showroom-management.git

3️⃣ Configure Database

Create a MySQL database

Import the provided SQL file:

mysql -u root -p showroom_db < database.sql

4️⃣ Configure Apache

Place project folder inside:

/var/www/html/


Update database credentials in config.php

5️⃣ Run the Application

Open browser and navigate to:

http://localhost/vehicle-showroom-management

📂 Project Structure
vehicle-showroom-management/
│
├── database/
│   └── database.sql
│
├── config/
│   └── config.php
│
├── public/
│   ├── index.php
│   ├── login.php
│   └── dashboard.php
│
├── assets/
│   ├── css/
│   └── js/
│
├── modules/
│   ├── vehicles/
│   ├── customers/
│   ├── sales/
│   ├── payments/
│   └── service/
│
└── README.md

🔐 Security Considerations

Input validation for forms

Prepared statements to prevent SQL Injection

Basic session-based authentication

📈 Future Enhancements

Role-based access control (Admin / Sales / Service)

Analytics dashboard

Invoice generation

Email/SMS service reminders

REST API support

🎓 Academic Relevance

This project demonstrates:

Practical implementation of DBMS concepts

Normalization and relational integrity

Real-world application of LAMP architecture

Full-stack web development fundamentals

👨‍💻 Author

Karthik S
B.Tech CSE

