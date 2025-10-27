<div align="center">
  <img src="assets/Screenshots/logo.png" alt="NOVEL NEST Logo" width="300"/>
</div>

# NOVEL NEST 📚

A modern, full-featured **Library Management System** built with PHP and SQLite. Designed for both users and admins, this system supports book management, lending, buying, attendance, news, messaging, and much more — ideal for university coursework and real-world use.

<div align="center">
  <img src="assets/logo.png" alt="NOVEL NEST Logo" width="300"/>
  <h1>NOVEL NEST 📚</h1>
  <p>A modern, full-featured <strong>Library Management System</strong> built with PHP and SQLite.</p>
</div>

<div align="center">
  <img src="https://img.shields.io/badge/Language-PHP_7.4%2B-8892BF?style=for-the-badge&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/Database-SQLite-003B57?style=for-the-badge&logo=sqlite" alt="SQLite Database">
  <img src="https://img.shields.io/badge/License-Educational%20Use-blue?style=for-the-badge" alt="License">
</div>

---

## 📑 Table of Contents

<details open>
  <summary><b>Expand Table of Contents</b></summary>
  <ul>
    <li><a href="#-project-overview">📱 Project Overview</a></li>
    <li><a href="#-key-features">✨ Key Features</a></li>
    <li><a href="#-prerequisites">✅ Prerequisites</a></li>
    <li><a href="#-setup-instructions">🚀 Setup Instructions</a></li>
    <li><a href="#-usage-and-default-logins">📝 Usage and Default Logins</a></li>
    <li><a href="#-database-schema">🗄 Database Schema</a></li>
    <li><a href="#-project-structure">🏗️ Project Structure</a></li>
    <li><a href="#-credits-and-license">👥 Credits and License</a></li>
  </ul>
</details>

---

## 📱 Project Overview

**NOVEL NEST** is a robust, full-stack **Library Management Portal** designed for both academic coursework and real-world application. It implements comprehensive features for managing books, users, lending/buying transactions, and internal communication (news, messaging, attendance).

### Technology Stack
| Component | Technology | Details |
| :--- | :--- | :--- |
| **Frontend** | HTML, CSS, JavaScript | Responsive web application |
| **Backend** | **PHP** | Core logic, routing, and database interaction |
| **Database** | **SQLite** (`nn_library.sqlite`) | Lightweight, file-based database |
| **Architecture** | **Full-stack** | User and Admin roles with role-based access control |

---

## ✨ Key Features

### 👤 For Users (Students/Members)
* 🔐 **Secure Authentication** – Register, login, and manage personal profile details.
* 📚 **Book Management** – View, search, filter, **Lend**, and **Buy** books with transaction history tracking.
* 💬 **Messaging System** – Directly contact branch administrators and receive replies within the platform.
* 📰 **News & Updates** – View official library news, and interact with posts via **reactions** and **comments**.
* ⭐ **Ratings & Statistics** – Track personal activity with counters for lent/bought books and overall activity-based rating.

### 🛠 For Admins (Librarians/Staff)
* 📊 **Dashboard Analytics** – Comprehensive overview of key metrics: total users, books, sales, and news activity.
* 👥 **User & Admin Management** – Full CRUD (Create, Read, Update, Delete) for managing users and staff accounts.
* 📋 **Book & Author Management** – Dedicated modules to add, edit, delete, and organize books, authors, and book categories.
* 🏢 **Branch Management** – Manage physical library branches, track book inventory, and log staff/user **attendance** per branch.
* 📬 **Communication** – Post new news updates and manage/reply to user messages.

### ⚙️ Technical Highlights
* 🎨 **Responsive UI** – Modern, mobile-friendly design.
* 📄 **File Handling** – Support for uploading and viewing book PDFs, cover images, and author photos.
* 🔍 **Advanced Search & Filters** – Quick, efficient lookup across all major entities (books, authors, news).

---

## ✅ Prerequisites

1.  **Web Server:** An environment like **XAMPP**, **MAMP**, or **WAMP** is recommended.
2.  **PHP:** Version **7.4+** or newer.
3.  **SQLite Extension:** Ensure the **PHP PDO SQLite extension** is enabled in your `php.ini` file.

---

## 🚀 Setup Instructions

Follow these steps to get NOVEL NEST running on your local machine.

### 1. Clone the Repository

Open your terminal and clone the project into your web server's root directory (e.g., `C:\xampp\htdocs`):

```bash
git clone <repo-url>
cd Novel-NEST-DBMS-Project

## 🏗️ Project Structure

.
├── index.php
├── admin.php
├── books.php
├── authors.php
├── users.php
├── news.php
├── profile.php
├── about.php
├── contact.php
├── assets/
│ ├── style.css
│ ├── logo.png
│ ├── Covers/
│ ├── Author/
│ └── Screenshots/
├── db/
│ ├── nn_library.sqlite
│ ├── schema.sql
│ └── books_sample.sql
├── includes/
│ └── db_connect.php

text

---

## 🚀 Setup Instructions

### 1. Clone the Repository

git clone <repo-url>
cd Novel-NEST-DBMS-Project

text

### 2. Configure PHP & SQLite

- Ensure PHP 7.4+ is installed with SQLite PDO extension enabled.  
- Place the project in your web server root directory (e.g., `htdocs` for XAMPP or MAMP).  

### 3. Database Setup

- The database schema is located in `db/schema.sql`.  
- On first run, the system auto-generates sample data if the database is empty.  
- Optionally, import `db/books_sample.sql` for additional book data.

### 4. Run the Application

- Open `index.php` in your browser.  
- Register as a user or login as admin (you may need to set up a default admin).

---

## 📝 Usage

### Users
- Register and login  
- View and update profiles  
- Lend or buy books  
- Send messages to admins  
- React to and comment on news

### Admins
- Login and access the admin dashboard  
- Manage users, books, authors, branches, news, and messages  
- Monitor system statistics and user ratings

---

## 🗄 Database Schema

The schema includes tables such as:

- `users`, `admins`  
- `books`, `authors`  
- `lend`, `buy`  
- `attendance`, `branches`  
- `news`, `messages`, `book_branches`  
- `news_comments`, `news_reacts` and more

Refer to the `db/schema.sql` file for detailed structure.

---

## 📸 Screenshots & Presentation

- Demo screenshots: `assets/Screenshots/`  
- Presentation slides: `assets/45_59_DBMS_Presentation.pptx`

---

## 👥 Credits

Designed by **Jubair Ahammad Akter** & **Ariful Islam** (CSEDU, University of Dhaka)  
More info: [about.php](about.php)

---

## 📄 License

For educational use only. You may adapt and extend it for personal projects.  
© 2025 NOVEL NEST. All rights reserved.

<div align="center">
Made with ❤️ for library enthusiasts and academic projects  
<br>
⬆ <a href="#novel-nest-">Back to Top</a> | 📑 Table of Contents
</div>
