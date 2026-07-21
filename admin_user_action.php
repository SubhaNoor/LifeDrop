<?php
session_start();
include 'db.php';

// 🔒 সিকিউরিটি চেক (শুধুমাত্র অ্যাডমিন ঢুকতে পারবে)
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$user_id    = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action     = isset($_GET['action']) ? $_GET['action'] : '';
$admin_user = $_SESSION['user'];

if ($user_id > 0 && $action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Activity Log এন্ট্রি
    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_name, action) VALUES (?, ?)");
    $log_action = "Deleted user account with ID #$user_id";
    $log_stmt->bind_param("ss", $admin_user, $log_action);
    $log_stmt->execute();
    $log_stmt->close();
}

header("Location: admin.php#users");
exit();
?>