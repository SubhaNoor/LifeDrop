<?php
include 'db.php'; // কানেকশন ফাইল যুক্ত করলাম

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $blood_group = $_POST['blood_group'];
    $password = $_POST['password']; // নিরাপত্তার জন্য পাসওয়ার্ড হ্যাশ (Hash) করা উচিত, আপাতত সহজ রাখলাম

    // ডেটাবেজে ডাটা ইনসার্ট করার কুয়েরি
    $sql = "INSERT INTO users (name, phone, blood_group, password) VALUES ('$name', '$phone', '$blood_group', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration Successful!'); window.location.href='login.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>