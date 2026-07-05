<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // ফোন নাম্বার ও পাসওয়ার্ড চেক করার কুয়েরি
    $sql = "SELECT * FROM users WHERE phone='$phone' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // সেশনে ফোন নম্বর এবং ইউজারের আসল নাম সেভ করছি
        $_SESSION['user'] = $row['phone'];
        $_SESSION['user_name'] = $row['name']; 
        
        // লগইন সফল হলে সরাসরি home.php-তে পাঠিয়ে দেবে
        echo "<script>alert('Login Successful!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Invalid Phone or Password!'); window.location.href='login.html';</script>";
    }
}
?>