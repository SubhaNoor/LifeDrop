<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$request_id = isset($_GET['id']) ? $_GET['id'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$current_user = $_SESSION['user'];

if (!empty($request_id) && ($action == 'accept' || $action == 'reject')) {
    if ($action == 'accept') {
        // ১. রিকোয়েস্ট আপডেট করা
        $conn->query("UPDATE blood_requests SET status='Accepted' WHERE id='$request_id' AND receiver_phone='$current_user'");
        
        // ২. ডোনারকে ৯০ দিনের জন্য লকআউট করা এবং ম্যানুয়াল স্ট্যাটাস সাময়িকভাবে ০ (Unavailable) করা
        $today = date('Y-m-d');
        $conn->query("UPDATE donors SET last_donation_date='$today', status=0 WHERE phone='$current_user'");
        
        echo "<script>alert('Request Accepted! You are locked for 90 days.'); window.location.href='home.php';</script>";
    } else {
        // Reject করলে রিকোয়েস্ট স্ট্যাটাস Rejected হয়ে যাবে
        $conn->query("UPDATE blood_requests SET status='Rejected' WHERE id='$request_id' AND receiver_phone='$current_user'");
        echo "<script>alert('Request Rejected.'); window.location.href='home.php';</script>";
    }
} else {
    header("Location: home.php");
}
?>