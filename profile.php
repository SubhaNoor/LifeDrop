<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$current_user = $_SESSION['user']; // লগইন থাকা ইউজার
$profile_phone = isset($_GET['phone']) ? $_GET['phone'] : ''; // যার প্রোফাইল দেখা হচ্ছে

if (empty($profile_phone)) {
    header("Location: home.php");
    exit();
}

// চেক করা হচ্ছে এটি নিজের প্রোফাইল কিনা
$is_own_profile = ($current_user === $profile_phone);

// প্রোফাইল ডেটা আপডেট প্রসেস (যদি নিজের প্রোফাইল হয় এবং ফর্ম সাবমিট করা হয়)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_own_profile) {
    $name = $_POST['name'];
    $area = $_POST['area'];
    $age = $_POST['age'];
    $is_smoker = $_POST['is_smoker'];
    $medical_notes = $_POST['medical_notes'];
    
    // ডাটাবেজে আপডেট কুয়েরি
    $update_stmt = $conn->prepare("UPDATE donors SET name=?, area=?, age=?, is_smoker=?, medical_notes=? WHERE phone=?");
    $update_stmt->bind_param("ssisss", $name, $area, $age, $is_smoker, $medical_notes, $profile_phone);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php?phone=$profile_phone';</script>";
    } else {
        echo "Error updating profile.";
    }
    $update_stmt->close();
}

// ডোনারের তথ্য ডাটাবেজ থেকে নিয়ে আসা
$stmt = $conn->prepare("SELECT * FROM donors WHERE phone = ?");
$stmt->bind_param("s", $profile_phone);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

// যদি সে ডোনার হিসেবে রেজিস্টার্ড না থাকে, তবে ইউজার টেবিল থেকে সাধারণ তথ্য আনা
if (!$donor) {
    $user_stmt = $conn->prepare("SELECT name, phone, blood_group FROM users WHERE phone = ?");
    $user_stmt->bind_param("s", $profile_phone);
    $user_stmt->execute();
    $donor = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
    $is_only_user = true;
} else {
    $is_only_user = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - LifeDrop</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
        .navbar { background-color: #dc2626; padding: 15px 40px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1e293b; margin-bottom: 20px; text-align: center; }
        .info-group { margin-bottom: 20px; text-align: left; }
        .info-group label { font-weight: bold; color: #475569; display: block; margin-bottom: 5px; }
        .info-group input, .info-group select, .info-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        .info-value { padding: 10px; background-color: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 16px; color: #1e293b; }
        .btn-update { background-color: #dc2626; color: white; border: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn-update:hover { background-color: #b91c1c; }
        .badge { background-color: #dc2626; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div style="font-weight: bold; font-size: 24px;">LifeDrop Droplet</div>
        <a href="home.php">Back to Dashboard</a>
    </div>

    <div class="container">
        <h2>
            <?php echo htmlspecialchars($donor['name']); ?>'s Profile 
            <?php if($is_own_profile) echo "<span class='badge'>You</span>"; ?>
        </h2>

        <?php if (!$donor): ?>
            <p style="text-align:center; color:#64748b;">Profile not found.</p>
        <?php else: ?>
            <form action="" method="POST">
                
                <div class="info-group">
                    <label>Full Name</label>
                    <?php if ($is_own_profile): ?>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($donor['name']); ?>" required>
                    <?php else: ?>
                        <div class="info-value"><?php echo htmlspecialchars($donor['name']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="info-group">
                    <label>Phone Number</label>
                    <div class="info-value"><?php echo htmlspecialchars($donor['phone']); ?></div>
                </div>

                <div class="info-group">
                    <label>Blood Group</label>
                    <div class="info-value" style="color: #dc2626; font-weight: bold;"><?php echo htmlspecialchars($donor['blood_group']); ?></div>
                </div>

                <?php if ($is_only_user): ?>
                    <p style="color: #64748b; font-style: italic; text-align: center; margin-top: 20px;">This user is not registered as a donor yet.</p>
                <?php else: ?>
                    <!-- যদি সে ডোনার হিসেবে রেজিস্টার্ড থাকে তবেই নিচের মেডিকেল ইনফো দেখাবে -->
                    <div class="info-group">
                        <label>Area / Location</label>
                        <?php if ($is_own_profile): ?>
                            <input type="text" name="area" value="<?php echo htmlspecialchars($donor['area']); ?>" required>
                        <?php else: ?>
                            <div class="info-value"><?php echo htmlspecialchars($donor['area']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="info-group">
                        <label>Age</label>
                        <?php if ($is_own_profile): ?>
                            <input type="number" name="age" value="<?php echo htmlspecialchars($donor['age']); ?>" required>
                        <?php else: ?>
                            <div class="info-value"><?php echo htmlspecialchars($donor['age']); ?> Years</div>
                        <?php endif; ?>
                    </div>

                    <div class="info-group">
                        <label>Smoker Status</label>
                        <?php if ($is_own_profile): ?>
                            <select name="is_smoker" required>
                                <option value="No" <?php if($donor['is_smoker'] == 'No') echo 'selected'; ?>>No, I don't smoke</option>
                                <option value="Yes" <?php if($donor['is_smoker'] == 'Yes') echo 'selected'; ?>>Yes, I smoke</option>
                            </select>
                        <?php else: ?>
                            <div class="info-value"><?php echo htmlspecialchars($donor['is_smoker']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="info-group">
                        <label>Medical Conditions / Notes</label>
                        <?php if ($is_own_profile): ?>
                            <textarea name="medical_notes" rows="3"><?php echo htmlspecialchars($donor['medical_notes']); ?></textarea>
                        <?php else: ?>
                            <div class="info-value" style="background-color: #fff1f2; border-color: #fecdd3; color: #9f1239;">
                                <?php echo nl2br(htmlspecialchars($donor['medical_notes'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_own_profile): ?>
                        <button type="submit" class="btn-update">💾 Update Profile</button>
                    <?php endif; ?>

                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>