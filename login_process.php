<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // 🔒 Prepared statement ব্যবহার করা হলো (আগে সরাসরি ভ্যারিয়েবল বসানো হতো, যেটা SQL Injection-এর ঝুঁকি ছিল)
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone=? AND password=?");
    $stmt->bind_param("ss", $phone, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $_SESSION['user'] = $row['phone'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['role'] = isset($row['role']) ? $row['role'] : 'user';

        if (!empty($row['profile_pic'])) {
            $_SESSION['profile_pic'] = $row['profile_pic'];
        }

        if ($_SESSION['role'] === 'admin') {
            echo "<script>alert('Login Successful! Welcome back, Admin.'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Login Successful!'); window.location.href='home.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid Phone or Password!'); window.location.href='login.html';</script>";
    }
    $stmt->close();
}
?>