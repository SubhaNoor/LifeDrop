<?php
$host = "localhost";
$user = "root";       // XAMPP-এর ডিফল্ট ইউজার
$pass = "";           // XAMPP-এর ডিফল্ট পাসওয়ার্ড (ফাঁকা থাকবে)
$dbname = "lifedrop_db";

// ডেটাবেজ কানেক্ট করা
$conn = new mysqli($host, $user, $pass, $dbname);

// কানেকশন চেক করা
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>