<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $blood_group = $_POST['blood_group'];
    $password = $_POST['password']; 

    // ১. আগে চেক করব এই ফোন নাম্বারে কোনো অ্যাকাউন্ট অলরেডি আছে কিনা (Prepared Statement)
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // ফোন নাম্বার অলরেডি থাকলে অ্যালার্ট দিয়ে ব্যাক করাবে
        echo "<script>alert('Error: This phone number is already registered!'); window.location.href='register.html';</script>";
    } else {
        // ২. যদি অ্যাকাউন্ট না থাকে, তবেই নতুন অ্যাকাউন্ট তৈরি হবে
        $insert_stmt = $conn->prepare("INSERT INTO users (name, phone, blood_group, password) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("ssss", $name, $phone, $blood_group, $password);
        
        if ($insert_stmt->execute()) {
            echo "<script>alert('Registration Successful!'); window.location.href='login.html';</script>";
        } else {
            echo "Error: Something went wrong.";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>