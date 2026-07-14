<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$current_user = $_SESSION['user']; 
$profile_phone = isset($_GET['phone']) ? $_GET['phone'] : ''; 

if (empty($profile_phone)) {
    header("Location: home.php");
    exit();
}

$is_own_profile = ($current_user === $profile_phone);

// 📸 প্রোফাইল ডেটা, ছবি এবং তথ্য আপডেট প্রসেস
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_own_profile) {
    $name = $_POST['name'];
    $area = isset($_POST['area']) ? $_POST['area'] : '';
    $age = !empty($_POST['age']) ? $_POST['age'] : null;
    $is_smoker = isset($_POST['is_smoker']) ? $_POST['is_smoker'] : 'No';
    $medical_notes = isset($_POST['medical_notes']) ? $_POST['medical_notes'] : '';
    $last_donation_date = !empty($_POST['last_donation_date']) ? $_POST['last_donation_date'] : null;
    
    // ১. ছবি আপলোড হ্যান্ডলিং (বুলেটপ্রুফ লজিক)
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        
        // যদি uploads ফোল্ডার না থাকে তবে অটোমেটিক তৈরি হবে
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
        // ইউনিক নাম তৈরি করা
        $image_name = time() . '_' . $profile_phone . '.' . $file_ext;
        $target_file = $target_dir . $image_name;
        
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                
                // users টেবিলে ছবি আপডেট
                $pic_user = $conn->prepare("UPDATE users SET profile_pic=? WHERE phone=?");
                $pic_user->bind_param("ss", $image_name, $profile_phone);
                $pic_user->execute();
                $pic_user->close();
                
                // donors টেবিলে ছবি আপডেট
                $pic_donor = $conn->prepare("UPDATE donors SET profile_pic=? WHERE phone=?");
                $pic_donor->bind_param("ss", $image_name, $profile_phone);
                $pic_donor->execute();
                $pic_donor->close();

                // ⚡ ন্যাভবারে ইন্সট্যান্ট দেখানোর জন্য সেশন ভেরিয়েবলও আপডেট করে দেওয়া হলো
                $_SESSION['profile_pic'] = $image_name; 
            }
        }
    }

    // ২. মেইন ইউজার টেবিল (users) আপডেট
    $update_user = $conn->prepare("UPDATE users SET name=? WHERE phone=?");
    $update_user->bind_param("ss", $name, $profile_phone);
    $update_user->execute();
    $update_user->close();

    // ৩. ডোনার টেবিল (donors) আপডেট
    $check_donor = $conn->prepare("SELECT id FROM donors WHERE phone=?");
    $check_donor->bind_param("s", $profile_phone);
    $check_donor->execute();
    $has_donor_row = $check_donor->get_result()->num_rows > 0;
    $check_donor->close();

    if ($has_donor_row) {
        $update_donor = $conn->prepare("UPDATE donors SET name=?, area=?, age=?, is_smoker=?, medical_notes=?, last_donation_date=? WHERE phone=?");
        $update_donor->bind_param("ssissss", $name, $area, $age, $is_smoker, $medical_notes, $last_donation_date, $profile_phone);
        $update_donor->execute();
        $update_donor->close();
    }
    
    echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php?phone=$profile_phone';</script>";
    exit();
}

// 🔍 ডাটাবেজ থেকে লেটেস্ট ডেটা রিড করা (যাতে রিফ্রেস করলে সাথে সাথে আসে)
$stmt = $conn->prepare("SELECT u.name, u.phone, u.blood_group, u.profile_pic, d.area, d.age, d.is_smoker, d.medical_notes, d.last_donation_date, d.id as donor_id FROM users u LEFT JOIN donors d ON u.phone = d.phone WHERE u.phone = ?");
$stmt->bind_param("s", $profile_phone);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_registered_donor = !empty($donor['donor_id']);

// 🩸 ডোনেশন হিস্ট্রি কুয়েরি
$history_stmt = $conn->prepare("SELECT r.id, u.name as receiver_name, u.phone as receiver_phone FROM blood_requests r JOIN users u ON r.sender_phone = u.phone WHERE r.receiver_phone = ? AND r.status = 'Accepted'");
$history_stmt->bind_param("s", $profile_phone);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$history_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($donor['name']); ?> - Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #F7F5F2;
            --ink: #17140F;
            --ink-soft: #57524A;
            --crimson: #C4123D;
            --crimson-dark: #8F0C2C;
            --crimson-tint: #FCE7EB;
            --card: #FFFFFF;
            --line: #E9E3DB;
            --success: #1F7A4D;
            --success-bg: #E3F5EA;
            --shadow: 0 10px 30px -14px rgba(23, 20, 15, 0.18);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image:
                radial-gradient(circle at 8% 0%, rgba(196, 18, 61, 0.06), transparent 40%),
                radial-gradient(circle at 100% 10%, rgba(196, 18, 61, 0.05), transparent 35%);
            color: var(--ink);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* ---------- Navbar ---------- */
        .navbar {
            background: linear-gradient(120deg, var(--crimson-dark), var(--crimson));
            padding: 14px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 500;
            box-shadow: 0 6px 20px -8px rgba(143, 12, 44, 0.5);
        }
        .navbar .brand {
            font-family: 'Sora', sans-serif;
            font-weight: 800;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 9px;
            letter-spacing: -0.01em;
        }
        .nav-right { display: flex; align-items: center; gap: 14px; }
        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            background: rgba(255,255,255,0.14);
            padding: 9px 16px;
            border-radius: 8px;
            transition: background-color 0.15s ease;
        }
        .navbar a:hover { background: rgba(255,255,255,0.24); }
        .nav-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.85);
        }

        /* ---------- Layout ---------- */
        .profile-layout {
            max-width: 940px;
            margin: 44px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 26px;
        }
        @media (min-width: 768px) {
            .profile-layout { grid-template-columns: 320px 1fr; align-items: start; }
        }

        .card {
            background: var(--card);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--line);
            padding: 32px;
            text-align: center;
        }

        /* ---------- Avatar card ---------- */
        .avatar-wrapper { position: relative; width: 132px; height: 132px; margin: 0 auto 20px auto; }
        .avatar {
            width: 100%; height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--crimson-tint);
        }
        .file-input-label {
            position: absolute;
            bottom: 2px; right: 2px;
            background: var(--crimson);
            color: white;
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 16px -4px rgba(196,18,61,0.6);
            font-size: 17px;
            border: 3px solid white;
            transition: transform 0.15s ease, background-color 0.15s ease;
        }
        .file-input-label:hover { background: var(--crimson-dark); transform: scale(1.06); }

        .profile-name {
            font-family: 'Sora', sans-serif;
            font-size: 21px;
            font-weight: 800;
            margin: 4px 0 8px 0;
            letter-spacing: -0.01em;
        }
        .blood-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--crimson-tint);
            color: var(--crimson);
            padding: 6px 16px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 14.5px;
        }
        .role-line {
            color: var(--ink-soft);
            font-size: 13.5px;
            margin-top: 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .role-line.is-donor { color: var(--success); font-weight: 700; }

        /* ---------- Info panel ---------- */
        .info-header {
            border-bottom: 1px solid var(--line);
            padding-bottom: 14px;
            margin-bottom: 22px;
            text-align: left;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            color: var(--ink);
            font-size: 17px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid { display: grid; grid-template-columns: 1fr; gap: 16px; text-align: left; }
        .form-grid.two-col { grid-template-columns: 1fr; }
        @media (min-width: 500px) { .form-grid.two-col { grid-template-columns: 1fr 1fr; } }

        .form-group { display: flex; flex-direction: column; gap: 7px; }
        .form-group label {
            font-weight: 700;
            color: var(--ink-soft);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .form-group input, .form-group select, .form-group textarea {
            padding: 12px 14px;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            background: #FCFBF9;
            color: var(--ink);
            box-sizing: border-box;
            width: 100%;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--crimson);
            box-shadow: 0 0 0 3px var(--crimson-tint);
            background: #fff;
        }
        .form-group textarea { resize: vertical; font-family: 'Inter', sans-serif; }

        .form-group .readonly-box {
            padding: 12px 14px;
            background: #F1EEE9;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            font-size: 15px;
            color: var(--ink-soft);
            font-weight: 500;
        }

        .btn-save {
            background: var(--crimson);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15.5px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 22px;
            transition: background-color 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
            box-shadow: 0 12px 24px -10px rgba(196, 18, 61, 0.5);
        }
        .btn-save:hover { background: var(--crimson-dark); transform: translateY(-1px); }

        /* ---------- History table ---------- */
        .history-table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 6px; }
        .history-table th {
            background: #FBF9F6;
            padding: 12px;
            color: var(--ink-soft);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 2px solid var(--line);
        }
        .history-table td { padding: 13px 12px; border-bottom: 1px solid var(--line); font-size: 14.5px; }
        .history-table tr:last-child td { border-bottom: none; }
        .history-table tr:hover td { background-color: #FCFAF8; }
        .status-success {
            background: var(--success-bg);
            color: var(--success);
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-block;
        }
        .empty-history {
            color: var(--ink-soft);
            font-style: italic;
            text-align: center;
            margin: 26px 0 10px;
            font-size: 14.5px;
        }

        @media (max-width: 640px) {
            .navbar { padding: 12px 18px; }
            .card { padding: 24px; border-radius: 16px; }
        }
    </style>
</head>
<body>

    <!-- 🔴 ন্যাভবার লজিক ফিক্সড: এখানেও এখন ডাটাবেজের ইমেজ দেখাবে -->
    <div class="navbar">
        <div class="brand">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="white"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg>
            LifeDrop Droplet
        </div>
        <div class="nav-right">
            <?php 
                // ডাটাবেজে ইমেজ থাকলে সেটা দেখাবে, না থাকলে ডিফল্ট ইমেজ
                $nav_avatar = (!empty($donor['profile_pic'])) ? 'uploads/' . $donor['profile_pic'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
            ?>
            <img src="<?php echo $nav_avatar; ?>" alt="Nav Avatar" class="nav-avatar">
            <a href="home.php">➜ Dashboard</a>
        </div>
    </div>

    <div class="profile-layout">
        
        <!-- 🔴 বাঁদিকের প্রোফাইল কার্ড -->
        <div class="card">
            <!-- ফর্মে ইমেজ সাবমিটের জন্য অনচেঞ্জ ট্রিগার যুক্ত করা হয়েছে -->
            <form id="profileForm" action="" method="POST" enctype="multipart/form-data">
                <div class="avatar-wrapper">
                    <?php 
                        $avatar_url = (!empty($donor['profile_pic'])) ? 'uploads/' . $donor['profile_pic'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                    ?>
                    <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="avatar">
                    
                    <?php if ($is_own_profile): ?>
                        <label for="profile_pic_input" class="file-input-label">📷</label>
                        <!-- ফাইল সিলেক্ট করার সাথে সাথেই যেন ফর্ম সাবমিট হয় তার ব্যবস্থা (onchange) -->
                        <input type="file" id="profile_pic_input" name="profile_pic" accept="image/*" style="display: none;" onchange="document.getElementById('profileForm').submit();">
                    <?php endif; ?>
                </div>
                
                <div class="profile-name"><?php echo htmlspecialchars($donor['name']); ?></div>
                <div class="blood-badge">🩸 <?php echo htmlspecialchars($donor['blood_group']); ?> Group</div>
                
                <p class="role-line<?php echo $is_registered_donor ? ' is-donor' : ''; ?>">
                    <?php echo $is_registered_donor ? "✨ Registered Blood Donor" : "General User (Not a Donor)"; ?>
                </p>
        </div>

        <!-- 🔵 ডানদিকের ফিক্সড ইনফরমেশন প্যানেল -->
        <div style="display: flex; flex-direction: column; gap: 26px;">
            
            <div class="card" style="text-align: left;">
                <div class="info-header">👤 Profile Information</div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <?php if ($is_own_profile): ?>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($donor['name']); ?>" required>
                        <?php else: ?>
                            <div class="readonly-box"><?php echo htmlspecialchars($donor['name']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grid two-col" style="margin-top: 16px;">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <div class="readonly-box"><?php echo htmlspecialchars($donor['phone']); ?></div>
                    </div>
                    <div class="form-group">
                        <label>Blood Group</label>
                        <div class="readonly-box" style="font-weight: 800; color: var(--crimson);"><?php echo htmlspecialchars($donor['blood_group']); ?></div>
                    </div>
                </div>

                <div class="form-grid two-col" style="margin-top: 16px;">
                    <div class="form-group">
                        <label>Area / Location</label>
                        <?php if ($is_own_profile): ?>
                            <input type="text" name="area" value="<?php echo htmlspecialchars($donor['area'] ?? ''); ?>" placeholder="e.g. Uttara, Dhaka">
                        <?php else: ?>
                            <div class="readonly-box"><?php echo htmlspecialchars($donor['area'] ?? 'Not Specified'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <?php if ($is_own_profile): ?>
                            <input type="number" name="age" value="<?php echo htmlspecialchars($donor['age'] ?? ''); ?>" min="18" max="65">
                        <?php else: ?>
                            <div class="readonly-box"><?php echo htmlspecialchars($donor['age'] ?? '--'); ?> Years</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grid two-col" style="margin-top: 16px;">
                    <div class="form-group">
                        <label>Smoker Status</label>
                        <?php if ($is_own_profile): ?>
                            <select name="is_smoker">
                                <option value="No" <?php if(($donor['is_smoker'] ?? 'No') == 'No') echo 'selected'; ?>>No, I don't smoke</option>
                                <option value="Yes" <?php if(($donor['is_smoker'] ?? '') == 'Yes') echo 'selected'; ?>>Yes, I smoke</option>
                            </select>
                        <?php else: ?>
                            <div class="readonly-box"><?php echo htmlspecialchars($donor['is_smoker'] ?? 'No'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Last Donation Date</label>
                        <?php if ($is_own_profile): ?>
                            <input type="date" name="last_donation_date" value="<?php echo htmlspecialchars($donor['last_donation_date'] ?? ''); ?>">
                        <?php else: ?>
                            <div class="readonly-box"><?php echo htmlspecialchars($donor['last_donation_date'] ?? 'No record'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grid" style="margin-top: 16px;">
                    <div class="form-group">
                        <label>Medical Conditions / Notes</label>
                        <?php if ($is_own_profile): ?>
                            <textarea name="medical_notes" rows="3"><?php echo htmlspecialchars($donor['medical_notes'] ?? ''); ?></textarea>
                        <?php else: ?>
                            <div class="readonly-box" style="background: #FCF3F3; border-color: var(--crimson-tint); min-height: 60px;">
                                <?php echo nl2br(htmlspecialchars($donor['medical_notes'] ?? 'None')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($is_own_profile): ?>
                    <button type="submit" class="btn-save">💾 Save Changes</button>
                <?php endif; ?>
            </form>
            </div>

            <!-- কার্ড ৩: ডোনেশন হিস্ট্রি -->
            <div class="card" style="text-align: left;">
                <div class="info-header">🩸 Donation History</div>
                <?php if ($history_result->num_rows > 0): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Recipient Name</th>
                                <th>Contact Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($history = $history_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($history['receiver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($history['receiver_phone']); ?></td>
                                    <td><span class="status-success">Donated ✓</span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty-history">No official donation records found yet.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>