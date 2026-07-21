<?php
session_start();
include 'db.php';

// 🔒 শুধুমাত্র admin role থাকলেই এই অ্যাকশন চালানো যাবে
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$donor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action   = isset($_GET['action']) ? $_GET['action'] : '';

if ($donor_id > 0 && $action === 'remove') {
    // ১. ডোনার ডিলিট করা
    $stmt = $conn->prepare("DELETE FROM donors WHERE id = ?");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $stmt->close();

    // ২. অ্যাক্টিভিটি লগে তথ্য যুক্ত করা
    $admin_user = $_SESSION['user'];
    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_name, action) VALUES (?, ?)");
    $log_action = "Removed donor with ID #$donor_id";
    $log_stmt->bind_param("ss", $admin_user, $log_action);
    $log_stmt->execute();
    $log_stmt->close();
}

header("Location: admin.php#donors");
exit();
?>