<?php
// admin.php - NOVEL NEST Admin Dashboard (Redesigned)
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { margin:0; font-family:sans-serif; background:#f8f8fa; }
        .header { display:flex; justify-content:space-between; align-items:center; padding:20px 40px; background:#222; color:#fff; }
        .logo { display:flex; align-items:center; }
        .logo img { height:48px; margin-right:16px; }
        .login-btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; }
        .carousel { margin:40px auto; max-width:1200px; }
        .carousel-title { font-size:2rem; margin-bottom:16px; }
        .carousel-row { display:flex; gap:24px; overflow-x:auto; }
        .carousel-item { min-width:200px; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:16px; text-align:center; transition:transform .2s; }
        .carousel-item:hover { transform:scale(1.05) rotateY(8deg); }
        .footer { background:#222; color:#fff; padding:32px 0 16px 0; text-align:center; }
        .footer .socials a { margin:0 12px; color:#fff; font-size:1.5rem; text-decoration:none; }
        .footer .links { margin:16px 0; }
        .footer .links a { margin:0 10px; color:#ff3366; text-decoration:none; }
        .footer .about { margin-top:16px; }
        .news-top-bar {
            background: #ff3366;
            color: #fff;
            padding: 16px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .news-top-bar h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
        }
        .news-top-bar div {
            margin-top: 8px;
            font-size: 1.1rem;
            opacity: .85;
        }
        .news-footer-bar {
            background: #18181b;
            color: #fff;
            padding: 24px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .footer-links a {
            color: #ff3366;
            text-decoration: none;
            font-size: 1rem;
        }
    </style>
</head>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; margin:0; }
        .admin-top-bar {
            background: linear-gradient(90deg, #ff3366 60%, #18181b 100%);
            color: #fff;
            padding: 24px 0 16px 0;
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 2px;
            box-shadow: 0 2px 8px #ff336633;
            position: relative;
        }
        .admin-top-bar .home-btn {
            position: absolute;
            left: 32px;
            top: 50%;
            transform: translateY(-50%);
            background: #fff;
            color: #ff3366;
            border-radius: 24px;
            padding: 8px 22px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 2px 8px #0002;
            border: none;
        }
        .admin-menu {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 18px;
            margin: 32px auto 24px auto;
            max-width: 900px;
        }
        .admin-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 6px #ff336622;
            padding: 22px 18px 18px 18px;
            width: 370px;
            min-width: 320px;
            max-width: 420px;
            text-align: left;
            transition: transform .15s, box-shadow .15s, background .15s, color .15s, border-color .15s;
            border: 2px solid #ff3366;
            position: relative;
            cursor: pointer;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 18px;
            text-decoration: none; /* Remove underline from link */
        }
        .admin-card svg {
            margin-bottom: 0;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
        }
        .admin-card-content {
            flex: 1;
            display: flex;
            align-items: center;
            font-size: 1.08rem;
            font-weight: bold;
            color: #ff3366;
        }
        .admin-card a {
            display: none;
        }
        .admin-card-title {
            font-size: 1.08rem;
            font-weight: bold;
            color: #ff3366;
            margin-left: 12px;
        }
        .admin-card:hover {
            transform: scale(1.05);
            background: #18181b;
            color: #fff;
            border-color: #18181b;
        }
        .admin-card:hover .admin-card-title {
            color: #fff;
        }
        .admin-card:hover svg {
            filter: brightness(0) invert(1);
        }
        .admin-card[data-hover]:hover:after {
            display: none;
        }
        .admin-card:visited,
        .admin-card:active,
        .admin-card:focus {
            text-decoration: none; /* Ensure underline never appears */
        }
        @media (max-width: 900px) {
            .admin-menu { gap: 10px; max-width: 98vw; }
            .admin-card { min-width: 120px; max-width: 98vw; width: 98vw; padding: 12px 6px 10px 10px; }
        }
    </style>
</head>
<body>
    <div class="admin-top-bar">
        <a href="index.php" class="home-btn">Go to Home</a>
        NOVEL NEST Admin Dashboard
    </div>
    <div class="admin-menu">
        <a href="users.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4Zm0-2a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Update User</span></div>
        </a>
        <a href="admin_list.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4Zm0-2a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/><circle cx="12" cy="8" r="4" fill="#18181b"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">View Admin</span></div>
        </a>
        <a href="admin_authors.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 14H7v-2h5v2Zm5-4H7v-2h10v2Zm0-4H7V7h10v2Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Update Writers</span></div>
        </a>
        <a href="admin_books.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 14H7v-2h5v2Zm5-4H7v-2h10v2Zm0-4H7V7h10v2Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Update Books</span></div>
        </a>
        <a href="admin_branches.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 14H7v-2h5v2Zm5-4H7v-2h10v2Zm0-4H7V7h10v2Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Update Attendance</span></div>
        </a>
        <a href="sales.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 15h-2v-2h2v2Zm0-4h-2V7h2v6Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Update Sold</span></div>
        </a>
        <a href="lend.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 14H7v-2h5v2Zm5-4H7v-2h10v2Zm0-4H7V7h10v2Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Update Lend</span></div>
        </a>
        <a href="admin_news.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24">
                <path fill="#ff3366" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 14H7v-2h5v2Zm5-4H7v-2h10v2Zm0-4H7V7h10v2Z"/>
                <circle cx="18" cy="6" r="2" fill="#ff3366"/>
            </svg>
            <div class="admin-card-content"><span class="admin-card-title">Update News</span></div>
        </a>
        <a href="admin_messages.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24">
                <path fill="#ff3366" d="M21 6.5a2.5 2.5 0 0 0-2.5-2.5h-13A2.5 2.5 0 0 0 3 6.5v11A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5v-11ZM5.5 5h13A1.5 1.5 0 0 1 20 6.5V7H4v-.5A1.5 1.5 0 0 1 5.5 5ZM4 8h16v9.5A1.5 1.5 0 0 1 18.5 19h-13A1.5 1.5 0 0 1 4 17.5V8Z"/>
            </svg>
            <div class="admin-card-content"><span class="admin-card-title">Message from Users</span></div>
        </a>
        <a href="logout.php" class="admin-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24"><path fill="#ff3366" d="M16 13v-2H7V8l-5 4 5 4v-3h9Zm3-10H5a2 2 0 0 0-2 2v6h2V5h14v14H5v-6H3v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/></svg>
            <div class="admin-card-content"><span class="admin-card-title">Log Out</span></div>
        </a>
    </div>
    <div class="news-footer-bar">
        <div class="footer-links">
            <a href="index.php">Home</a>
            <a href="contact.php">Contact</a>
            <a href="about.php">About Us</a>
            <a href="news.php">News</a>
        </div>
        <div style="margin-top:12px;font-size:.95rem;opacity:.8;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div>
    <!-- <div class="admin-bottom-bar">
        <div class="menu-links">
            <a href="index.php">Home</a>
            <a href="users.php">Users</a>
            <a href="admin_books.php">Books</a>
            <a href="admin_authors.php">Writers</a>
            <a href="admin_branches.php">Branches</a>
            <a href="sales.php">Sales</a>
        </div>
        <div style="margin-top:8px;font-size:.95rem;opacity:.8;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div> -->
</body>
</html>
