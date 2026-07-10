<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM users WHERE phone='$phone' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        
        $_SESSION['user'] = $row['phone'];
        $_SESSION['user_name'] = $row['name']; 
        
       
        echo "<script>alert('Login Successful!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Invalid Phone or Password!'); window.location.href='login.html';</script>";
    }
}
?>