<?php
// delete_user.php - Handles user deletion by admin
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (isset($_POST['delete_all'])) {
    // Delete all users with role 'user'
    $db->exec("DELETE FROM users WHERE role = 'user'");
    header('Location: users.php?role=user&msg=All users deleted');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id == $_SESSION['user_id']) {
        header('Location: users.php?msg=cannot_delete_self');
        exit;
    }
    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
    header('Location: users.php?msg=deleted');
    exit;
} else {
    header('Location: users.php?msg=invalid');
    exit;
}
