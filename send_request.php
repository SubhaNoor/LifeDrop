<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$sender = $_SESSION['user'];
$receiver = isset($_GET['donor_phone']) ? $_GET['donor_phone'] : '';

if (!empty($receiver)) {
    // ডুপ্লিকেট পেন্ডিং রিকোয়েস্ট চেক
    $check = $conn->query("SELECT id FROM blood_requests WHERE sender_phone='$sender' AND receiver_phone='$receiver' AND status='Pending'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO blood_requests (sender_phone, receiver_phone, status) VALUES ('$sender', '$receiver', 'Pending')");
        echo "<script>alert('Blood request sent successfully!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Request already pending!'); window.location.href='home.php';</script>";
    }
} else {
    header("Location: home.php");
}
?>