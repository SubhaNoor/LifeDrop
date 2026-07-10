<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['donor_name'];
    $phone = $_POST['donor_phone']; // এটি এখন সরাসরি সেশনের নম্বর থেকেই আসবে
    $area = $_POST['donor_area'];
    $blood = $_POST['donor_blood'];
    $age = $_POST['donor_age'];
    $is_smoker = $_POST['is_smoker'];
    $medical_notes = !empty($_POST['medical_notes']) ? $_POST['medical_notes'] : 'None';
    $last_donation = !empty($_POST['last_donation_date']) ? $_POST['last_donation_date'] : null;

    // সিকিউরিটি চেক: ফর্মের ফোন নম্বর আর সেশনের ফোন নম্বর এক কিনা
    if ($phone !== $_SESSION['user']) {
        echo "<script>alert('Unauthorized Action!'); window.location.href='home.php';</script>";
        exit();
    }

    // অলরেডি রেজিস্টার্ড কিনা চেক
    $check = $conn->prepare("SELECT id FROM donors WHERE phone = ?");
    $check->bind_param("s", $phone);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('You are already registered as a donor!'); window.location.href='home.php';</script>";
        exit();
    }

    // নতুন ডেটাসহ ইনসার্ট কুয়েরি
    $stmt = $conn->prepare("INSERT INTO donors (name, phone, blood_group, age, is_smoker, medical_notes, area, last_donation_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssissss", $name, $phone, $blood, $age, $is_smoker, $medical_notes, $area, $last_donation);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for registering as a donor!'); window.location.href='home.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>