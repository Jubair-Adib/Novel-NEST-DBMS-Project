<div align="center">
  <img src="assets/logo.png" alt="NOVEL NEST Logo" width="300"/>
</div>

# NOVEL NEST 📚

A modern, full-featured **Library Management System** built with PHP and SQLite. Designed for both users and admins, this system supports book management, lending, buying, attendance, news, messaging, and much more — ideal for university coursework and real-world use.

---

## 📑 Table of Contents

<details open>
  <summary><b>Expand Table of Contents</b></summary>
  - [Project Overview](#-project-overview)
  - [Features](#✨-features)
  - [Project Structure](#-project-structure)
  - [Setup Instructions](#-setup-instructions)
  - [Usage](#-usage)
  - [Database Schema](#-database-schema)
  - [Screenshots & Presentation](#-screenshots--presentation)
  - [Credits](#-credits)
  - [License](#-license)
</details>

---

## 📱 Project Overview

NOVEL NEST is a full-stack library management portal featuring:

- **Frontend:** PHP web app with responsive design  
- **Backend:** PHP & SQLite database  
- **Database:** SQLite file `nn_library.sqlite`  
- **User Roles:** User and Admin with role-based access control  
- **File Handling:** Support for PDF books, cover images, and author photos  
- **Interactive Modules:** Book lending, buying, attendance, news, messaging  

---

## ✨ Features

### 👤 For Users
- 🔐 Secure Authentication: Registration, login, profile management  
- 📚 Book Lending & Buying with history tracking  
- 👩‍🏫 Author Profiles and associated book lists  
- 💬 Messaging system to communicate with branch admins  
- 📰 News & Updates with reactions and comments  
- ⭐ Ratings & Statistics based on user activity  

### 🛠 For Admins
- 📊 Dashboard analytics tracking users, books, sales, news  
- 👥 Manage users and admin accounts  
- 📋 Add, edit, delete books and authors  
- 🏢 Manage branches including books and attendance  
- 📰 Post news and respond to user messages  

### ⚙️ Technical Highlights
- 🎨 Responsive UI, mobile-friendly  
- 📄 Upload and download PDF books  
- 🔍 Search and filter for books, authors, news  
- 🗄 Lightweight SQLite database for easy setup  
- 📈 Track user and system activity statistics  

---

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
