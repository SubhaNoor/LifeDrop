<?php
session_start();
include 'db.php';

// 🔒 শুধু admin ব্যবহার করতে পারবে
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$request_id = isset($_GET['id']) ? $_GET['id'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!empty($request_id) && ($action === 'accept' || $action === 'reject')) {

    // রিকোয়েস্টটার receiver_phone বের করা হচ্ছে (ডোনার কে সেটা জানতে)
    $find = $conn->prepare("SELECT receiver_phone FROM blood_requests WHERE id = ?");
    $find->bind_param("i", $request_id);
    $find->execute();
    $req_row = $find->get_result()->fetch_assoc();
    $find->close();

    if ($req_row) {
        $receiver_phone = $req_row['receiver_phone'];

        if ($action === 'accept') {
            $upd = $conn->prepare("UPDATE blood_requests SET status='Accepted' WHERE id = ?");
            $upd->bind_param("i", $request_id);
            $upd->execute();
            $upd->close();

            // ডোনারকে ৯০ দিনের জন্য লকআউট করা
            $today = date('Y-m-d');
            $lock = $conn->prepare("UPDATE donors SET last_donation_date=?, status=0 WHERE phone=?");
            $lock->bind_param("ss", $today, $receiver_phone);
            $lock->execute();
            $lock->close();
        } else {
            $upd = $conn->prepare("UPDATE blood_requests SET status='Rejected' WHERE id = ?");
            $upd->bind_param("i", $request_id);
            $upd->execute();
            $upd->close();
        }
    }
}

header("Location: admin.php");
exit();
?>