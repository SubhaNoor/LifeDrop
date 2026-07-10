<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_name']) && !isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$user_phone = $_SESSION['user'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user'];

// ডোনারের বর্তমান ম্যানুয়াল স্ট্যাটাস ও লকআউট তথ্য চেক করা
$donor_query = $conn->query("SELECT status, last_donation_date FROM donors WHERE phone='$user_phone'");
$is_donor = $donor_query->num_rows > 0;
$donor_status = 1;
$is_locked = false;

if ($is_donor) {
    $donor_data = $donor_query->fetch_assoc();
    $donor_status = $donor_data['status'];
    
    if (!empty($donor_data['last_donation_date'])) {
        $days = (strtotime(date('Y-m-d')) - strtotime($donor_data['last_donation_date'])) / (60 * 60 * 24);
        if ($days < 90) {
            $is_locked = true;
        }
    }
}

// ডোনারের নিজের স্ট্যাটাস নিজে টগল করার প্রসেস
if (isset($_GET['toggle_status']) && $is_donor && !$is_locked) {
    $new_status = ($donor_status == 1) ? 0 : 1;
    $conn->query("UPDATE donors SET status=$new_status WHERE phone='$user_phone'");
    header("Location: home.php");
    exit();
}

// ইনবাউন্ড রিকোয়েস্ট চেক করা (অন্যরা যে রিকোয়েস্ট পাঠিয়েছে)
$incoming_requests = $conn->query("SELECT r.id, u.name, u.phone, u.blood_group, r.status FROM blood_requests r JOIN users u ON r.sender_phone = u.phone WHERE r.receiver_phone='$user_phone' AND r.status='Pending'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeDrop - Dashboard</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
        .navbar { background-color: #dc2626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar .logo { font-size: 24px; font-weight: bold; }
        .navbar a, .navbar .welcome-text { color: white; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .navbar .logout-btn { background-color: white; color: #dc2626; padding: 5px 12px; border-radius: 4px; margin-left: 10px; }
        .container { max-width: 850px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        h1, h2 { color: #1e293b; }
        .search-box, .donor-form { display: flex; gap: 15px; justify-content: center; margin-top: 30px; flex-wrap: wrap; }
        select, input, .btn { padding: 12px 20px; font-size: 16px; border: 1px solid #cbd5e1; border-radius: 6px; }
        select, input { width: 220px; }
        .btn { background-color: #dc2626; color: white; border: none; cursor: pointer; font-weight: bold; }
        .btn:hover { background-color: #b91c1c; }
        .btn-all-donors { background-color: #1e293b; color: white; text-decoration: none; display: inline-block; border-radius: 6px; font-weight: bold; padding: 12px 20px; font-size: 16px; }
        .donor-section, .request-section { border-top: 2px dashed #cbd5e1; margin-top: 40px; padding-top: 30px; }
        .status-panel { background-color: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .status-on { background-color: #dcfce7; color: #166534; }
        .status-off { background-color: #fee2e2; color: #991b1b; }
        .status-btn { background-color: #475569; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #cbd5e1; }
        .acc-btn { background-color: #166534; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-right: 5px; }
        .rej-btn { background-color: #991b1b; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">LifeDrop Droplet</div>
        <div>
            <a href="home.php">Dashboard</a>
            <!-- নতুন দুইটা অপশন ন্যাভবারে যুক্ত করা হলো -->
            <a href="donated.php" style="color: #fef08a;">💝 DONATED</a>
            <a href="received.php" style="color: #93c5fd;">📩 RECEIVED</a>
<a href="profile.php?phone=<?php echo $user_phone; ?>" style="color: #fef08a;">👋 Hello, <?php echo htmlspecialchars($user_name); ?></a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        
        <!-- ডোনার স্ট্যাটাস কন্ট্রোল প্যানেল -->
        <?php if ($is_donor): ?>
            <div class="status-panel">
                <div>
                    <span style="font-weight: bold; color: #475569;">Your Donor Availability: </span>
                    <?php if ($is_locked): ?>
                        <span class="status-badge status-off">🔒 Locked (Cooldown)</span>
                    <?php elseif ($donor_status == 1): ?>
                        <span class="status-badge status-on">🟢 Available</span>
                    <?php else: ?>
                        <span class="status-badge status-off">🔴 Unavailable</span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (!$is_locked): ?>
                        <a href="home.php?toggle_status=1" class="status-status-btn status-btn">
                            <?php echo ($donor_status == 1) ? "Turn Off Status" : "Turn On Status"; ?>
                        </a>
                    <?php else: ?>
                        <small style="color: #64748b;">Locked after donation</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <h1>Find Blood Donors in Real-Time</h1>
        <p style="color: #64748b;">Select blood group and area to find available donors instantly.</p>

        <form action="donor.php" method="GET" class="search-box">
            <select name="blood_group" required>
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>
            <input type="text" name="area" placeholder="Enter Area (e.g. Uttara)" required>
            <button type="submit" class="btn">Search Donors</button>
            <a href="donor.php?view=all" class="btn-all-donors">📋 All Available Donors</a>
        </form>

        <!-- ইনকামিং ব্লাড রিকোয়েস্ট বোর্ড -->
        <?php if ($incoming_requests->num_rows > 0): ?>
            <div class="request-section">
                <h2>🚨 Pending Blood Requests For You</h2>
                <table>
                    <tr>
                        <th>Patient/Receiver</th>
                        <th>Blood Group</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                    <?php while($req = $incoming_requests->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['name']); ?></td>
                        <td style="font-weight:bold; color:#dc2626;"><?php echo htmlspecialchars($req['blood_group']); ?></td>
                        <td><?php echo htmlspecialchars($req['phone']); ?></td>
                        <td>
                            <a href="handle_request.php?id=<?php echo $req['id']; ?>&action=accept" class="acc-btn">✓ Accept</a>
                            <a href="handle_request.php?id=<?php echo $req['id']; ?>&action=reject" class="rej-btn">✕ Reject</a>
                        </td>
                    </tr>
                    <?php endempty; // টাইপো ফিক্স করে endwhile করা হলো ?>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- home.php এর ভেতরের নতুন ডোনার ফর্ম অংশ -->
        <div class="donor-section">
            <h2>Register as a Blood Donor</h2>
            <p style="color: #64748b;">Want to save lives? Drop your info below to become a donor.</p>
            <form action="add_donor.php" method="POST" class="donor-form">
                <input type="text" name="donor_name" placeholder="Full Name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                
                <!-- ফোন নম্বর লক করা, সেশন থেকে অটোমেটিক বসবে -->
                <input type="tel" name="donor_phone" value="<?php echo htmlspecialchars($user_phone); ?>" readonly style="background-color: #e2e8f0; cursor: not-allowed;" title="You can only register yourself as a donor">
                
                <input type="text" name="donor_area" placeholder="Area (e.g. Uttara)" required>
                <input type="date" name="last_donation_date" placeholder="Last Donation Date (If any)">
                
                <!-- নতুন ইনপুট ফিল্ডসমূহ -->
                <input type="number" name="donor_age" placeholder="Your Age" min="18" max="65" required style="width: 220px;">
                
                <select name="is_smoker" required>
                    <option value="">Do you smoke?</option>
                    <option value="No">No, I don't smoke</option>
                    <option value="Yes">Yes, I smoke</option>
                </select>

                <select name="donor_blood" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
                
                <textarea name="medical_notes" placeholder="Medical Notes / Other Diseases (If any, e.g. Diabetes, High BP, etc.)" style="width: 100%; max-width: 480px; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: Arial; font-size: 16px; margin-top: 10px;"></textarea>
                
                <button type="submit" class="btn" style="width: 100%; max-width: 240px; margin-top: 10px;">Become a Donor</button>
            </form>
        </div>
    </div>

</body>
</html>