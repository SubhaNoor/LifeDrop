<?php
include 'db.php';
session_start();

// 🔒 অ্যাডমিন হওয়ার একমাত্র চাবি — এই কোডটা ফিক্সড এবং শুধু সার্ভার সাইডে থাকে
define('ADMIN_SECRET_CODE', 'apala');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = $_POST['name'];
    $phone       = $_POST['phone'];
    $blood_group = $_POST['blood_group'];
    $password    = $_POST['password'];
    $admin_code  = isset($_POST['admin_code']) ? trim($_POST['admin_code']) : '';

    // 🛡️ রোল নির্ধারণ — এখানেই মূল সিকিউরিটি লজিক
    $role = 'user';

    if ($admin_code !== '') {
        if ($admin_code === ADMIN_SECRET_CODE) {
            $role = 'admin';
        } else {
            // ভুল কোড দিলে রেজিস্ট্রেশনই হবে না, ফর্মে ফেরত পাঠিয়ে এরর দেখানো হবে
            header("Location: register.html?admin_error=1");
            exit();
        }
    }

    // ১. আগে চেক করব এই ফোন নাম্বারে কোনো অ্যাকাউন্ট অলরেডি আছে কিনা (Prepared Statement)
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // ফোন নাম্বার অলরেডি থাকলে অ্যালার্ট দিয়ে ব্যাক করাবে
        echo "<script>alert('Error: This phone number is already registered!'); window.location.href='register.html';</script>";
    } else {
        // ২. যদি অ্যাকাউন্ট না থাকে, তবেই নতুন অ্যাকাউন্ট তৈরি হবে (role সহ)
        $insert_stmt = $conn->prepare("INSERT INTO users (name, phone, blood_group, password, role) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $name, $phone, $blood_group, $password, $role);

        if ($insert_stmt->execute()) {
            if ($role === 'admin') {
                echo "<script>alert('Admin account created successfully!'); window.location.href='login.html';</script>";
            } else {
                echo "<script>alert('Registration Successful!'); window.location.href='login.html';</script>";
            }
        } else {
            echo "Error: Something went wrong.";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>