# NOVEL NEST - Library Management System

NOVEL NEST is a modern, full-featured library management portal built with PHP and SQLite. It supports user and admin roles, book management, lending, buying, attendance, news, messaging, and more. The project is designed for DBMS coursework and is suitable for university-level demonstration and real-world adaptation.

## Features
- **User & Admin Authentication**: Secure login, profile management, and role-based access.
- **Book Management**: Add, edit, delete, and search books. PDF support and branch-wise stock.
- **Author Management**: Add, edit, delete authors. Author profiles and book lists.
- **Lending & Buying**: Users can lend and buy books, view history, and download receipts.
- **Attendance Tracking**: Branch-wise entry/exit logs for users.
- **News System**: Post, edit, delete news. Users can react and comment.
- **Messaging**: Users can send messages to branch admins; admins can reply.
- **Admin Dashboard**: Manage users, books, authors, branches, sales, news, and messages.
- **Statistics & Ratings**: User ratings based on activity; counters for users, books, sales, news, authors, and PDFs.
- **Responsive UI**: Modern, mobile-friendly design with custom CSS and images.

## Project Structure
```
├── about.php
├── admin.php
├── authors.php
├── books.php
├── contact.php
├── index.php
├── news.php
├── profile.php
├── users.php
├── ... (other admin/user modules)
├── assets/
│   ├── style.css
│   ├── logo.png
│   ├── Covers/
│   ├── Author/
│   ├── about/
│   └── ...
├── db/
│   ├── nn_library.sqlite
│   ├── schema.sql
│   └── books_sample.sql
├── includes/
│   └── db_connect.php
```

## Setup Instructions
1. **Clone or Download** the repository to your local machine.
2. **Database Setup**:
   - The project uses SQLite. The schema is in `db/schema.sql`.
   - On first run, sample data is auto-generated if the database is empty.
   - You can manually import `books_sample.sql` for more book data.
3. **Configure PHP**:
   - Ensure PHP 7.4+ is installed with SQLite PDO extension.
   - Place the project in your web server's root (e.g., `htdocs` for XAMPP/MAMP).
4. **Assets**:
   - All images, covers, and author photos are in the `assets/` folder.
5. **Run the App**:
   - Open `index.php` in your browser.
   - Register as a user or login as admin (default admin setup required).

## Usage
- **Users**: Register, login, view/update profile, lend/buy books, view history, send messages, react/comment on news.
- **Admins**: Login, access dashboard, manage users, books, authors, branches, news, and messages.
- **Contact & About**: View branch contact list and designer info.

## Database Schema
- See `db/schema.sql` for all tables: users, books, authors, lend, buy, attendance, amounts, branches, news, admins, messages, book_branches, news_comments, news_reacts, etc.

## Screenshots & Presentation
- See `assets/Screenshots/` and `assets/45_59_DBMS_Presentation.pptx` for demo images and slides.

## Credits
- Designed by Jubair Ahammad Akter & Ariful Islam (CSEDU, University of Dhaka)
- See `about.php` for more info and contact links.

## License
This project is for educational use. You may adapt and extend it for your own needs.

---
© 2025 NOVEL NEST. All rights reserved.
