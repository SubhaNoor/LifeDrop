<?php
session_start();
include 'db.php';

// 🔒 সিকিউরিটি চেক (শুধুমাত্র অ্যাডমিন ঢুকতে পারবে)
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action     = isset($_GET['action']) ? $_GET['action'] : '';
$admin_user = $_SESSION['user'];

if ($request_id > 0 && !empty($action)) {
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE blood_requests SET status = 'Approved' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        // Activity Log এন্ট্রি
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_name, action) VALUES (?, ?)");
        $log_action = "Approved blood request #$request_id";
        $log_stmt->bind_param("ss", $admin_user, $log_action);
        $log_stmt->execute();
        $log_stmt->close();

    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE blood_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        // Activity Log এন্ট্রি
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_name, action) VALUES (?, ?)");
        $log_action = "Rejected blood request #$request_id";
        $log_stmt->bind_param("ss", $admin_user, $log_action);
        $log_stmt->execute();
        $log_stmt->close();

    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM blood_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        // Activity Log এন্ট্রি
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_name, action) VALUES (?, ?)");
        $log_action = "Deleted blood request #$request_id";
        $log_stmt->bind_param("ss", $admin_user, $log_action);
        $log_stmt->execute();
        $log_stmt->close();
    }
}

header("Location: admin.php#requests");
exit();
?>