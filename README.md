# 🍔 AnnSatu - Online Food Ordering System

AnnSatu is a web-based food ordering system developed using **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**. The system allows users to browse food items, place orders, make online payments using **eSewa**, and enables administrators to manage the website through an admin panel.

---

## 📌 Project Overview

The AnnSatu project is designed to provide customers with an easy and convenient way to order food online. Customers can browse the menu, register an account, place orders, and complete payments through the integrated eSewa payment gateway.

The admin panel allows administrators to manage food items, customer orders, messages, and website content efficiently.

---

## 🚀 Features

### 👤 User Module

- User Registration
- User Login
- Browse Food Menu
- Food Details
- Add to Cart
- Place Orders
- Contact Form
- Responsive Design
- eSewa Online Payment
- Order Confirmation

---

### 🔑 Admin Module

- Admin Login
- Dashboard
- Manage Food Menu
- Manage Orders
- Manage Customers
- Manage Contact Messages
- Upload Food Images
- Website Management

---

## 💳 Payment Gateway

This project integrates **eSewa Payment Gateway** for secure online transactions.

### Payment Flow

1. Customer places an order.
2. Redirects to eSewa Payment.
3. Payment is verified.
4. Success page is displayed.
5. Order is confirmed.

---

## 🛠 Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript

### Backend

- PHP

### Database

- MySQL

### Server

- XAMPP

### Payment Gateway

- eSewa ePay

### Version Control

- Git
- GitHub

---

## 📁 Project Structure

```
AnnSatu/
│
├── admin/
├── uploads/
├── tests/
├── config/
├── about.php
├── index.php
├── menu.php
├── order.php
├── contact.php
├── login.php
├── signup.php
├── checkout_esewa.php
├── success_esewa.php
├── navbar.php
├── style.css
├── script.js
├── requirements.txt
├── package.json
└── README.md
```

---

## ⚙ Installation

### 1. Clone Repository

```bash
git clone https://github.com/UFO36-9/AnnaSatu.git
```

---

### 2. Move Project

Copy the project folder into

```
xampp/htdocs/
```

---

### 3. Start XAMPP

Start

- Apache
- MySQL

---

### 4. Import Database

Open

```
http://localhost/phpmyadmin
```

Create a database

```
annsatu
```

Import the SQL file into the database.

---

### 5. Configure Database

Update your database connection inside

```php
db.php
```

Example:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "annsatu";
```

---

### 6. Run the Project

Open your browser

```
http://localhost/AnnaSatu
```

---

## 🧪 Testing

The project includes automated Selenium test scripts located inside:

```
tests/
```

Run tests using:

```bash
pytest
```

---

## 📷 Screens

- Home Page
- Menu Page
- About Page
- Contact Page
- Login & Registration
- Checkout
- eSewa Payment
- Admin Dashboard

---

## 📦 Required Software

- XAMPP
- PHP 8+
- MySQL
- Python (For Automation Testing)
- Selenium
- PyTest
- Git

---

## 📚 Future Enhancements

- Khalti Payment Integration
- Stripe Payment
- Email Notifications
- Order Tracking
- Customer Reviews
- Discount Coupons
- Mobile Application
- Delivery Status Tracking

---

## 👨‍💻 Developer

**Roshan Manandhar**

Bachelor in Computer Applications (BCA)

GitHub:
https://github.com/RoshanManandhar

---

## 📄 License

This project is developed for educational and learning purposes.

---

## ⭐ Support

If you found this project useful, please consider giving it a ⭐ on GitHub.

Happy Coding! 🚀
